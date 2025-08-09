<?php

namespace App\Controller\Web;

use App\Entity\Web\Offer;
use App\Entity\Web\OfferUnlock;
use App\Entity\Web\Payment;
use App\Entity\Web\Quote;
use App\Form\Web\QuoteType;
use App\Repository\Web\CategoryRepository;
use App\Repository\Web\OfferRepository;
use App\Repository\Web\OfferUnlockRepository;
use App\Repository\Web\PaymentRepository;
use App\Repository\Web\QuoteRepository;
use App\Repository\Web\ServiceProviderAddressRepository;
use App\Repository\Web\ServiceProviderContactRepository;
use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use InvalidArgumentException;

#[Route('/{_locale<%app.supported_locales%>}/', name: 'app_web_')]
class QuoteController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private readonly QuoteRepository $quotes,
        private readonly CategoryRepository $categories,
        private readonly OfferRepository $offers,
        private readonly OfferUnlockRepository $unlocks,
        private readonly ServiceProviderContactRepository $spContacts,
        private readonly ServiceProviderAddressRepository $spAddresses,
        private readonly PaymentRepository $payments,
    ) {}

    #[Route('poptavky', name: 'search', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $open = $this->quotes->createQueryBuilder('q')
            ->andWhere('q.status = :s')->setParameter('s', Quote::STATUS_OPEN)
            ->orderBy('q.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()->getResult();

        return $this->render('web/quote/list.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'quotes' => $open,
        ]);
    }

    #[Route('poptavka/nova', name: 'quote_new', methods: ['GET','POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request): Response
    {
        $quote = new Quote();
        $quote->addItem(new \App\Entity\Web\QuoteItem());

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        $quote->setUser($user);

        $form = $this->createForm(QuoteType::class, $quote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->quotes->add($quote, true);
            $this->addFlash('success', 'Poptávka byla založena.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $quote->getId(), '_locale' => $request->getLocale()]);
        }

        return $this->render('web/quote/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'form' => $form,
        ]);
    }

    #[Route('poptavka/{id}', name: 'quote_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, int $id): Response
    {
        $quote = $this->quotes->find($id);
        if (!$quote) { throw $this->createNotFoundException('Poptávka nenalezena.'); }

        /** @var \App\Entity\Web\User|null $user */
        $user = $this->getUser();
        $isOwner = $user && $quote->getUser()->getId() === $user->getId();

        $unlockActive = false;
        $revealContact = null;
        $revealAddress = null;

        if ($isOwner && $quote->getChosenOffer()) {
            $unlockActive = (bool) $this->unlocks->findOneBy([
                'offer'    => $quote->getChosenOffer(),
                'customer' => $user,
                'status'   => \App\Entity\Web\OfferUnlock::STATUS_ACTIVE,
            ]);

            if ($unlockActive) {
                $sp = $quote->getChosenOffer()->getServiceProvider();
                $revealContact = $this->spContacts->findPreferredForProvider($quote->getChosenOffer()->getServiceProvider());
                $revealAddress = $this->spAddresses->findPrimary($sp);
            }
        }

        return $this->render('web/quote/show.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService'  => $this->projectService->getProject(),
            'quote'           => $quote,
            'isOwner'         => $isOwner,
            'unlockActive'    => $unlockActive,
            'revealContact'   => $revealContact,
            'revealAddress'   => $revealAddress,
        ]);
    }

    #[Route('poptavka/{id}/nabidka/{offerId}/vybrat', name: 'quote_choose_offer', requirements: ['id' => '\d+', 'offerId' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function chooseOffer(Request $request, int $id, int $offerId): Response
    {
        $quote = $this->quotes->find($id);
        if (!$quote) {
            throw $this->createNotFoundException('Poptávka nenalezena.');
        }

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        if ($quote->getUser()->getId() !== $user->getId()) {
            $this->addFlash('danger', 'Nemáš oprávnění k této poptávce.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        // CSRF
        if (!$this->isCsrfTokenValid('choose_offer_'.$offerId, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Neplatný CSRF token.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        // Najdi nabídku a ověř, že patří do stejné poptávky a je aktivní
        $offer = $this->offers->find($offerId);
        if (!$offer || $offer->getQuote()->getId() !== $quote->getId()) {
            $this->addFlash('warning', 'Nabídka nebyla nalezena.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }
        if ($offer->getStatus() !== Offer::STATUS_ACTIVE) {
            $this->addFlash('warning', 'Tuto nabídku nelze vybrat.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }
        if ($quote->getStatus() === 'closed' || $quote->getStatus() === 'cancelled') {
            $this->addFlash('warning', 'Tato poptávka už není otevřená.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        $alreadyChosen = $quote->getChosenOffer();

        // Pokud už byla vybraná jiná nabídka → povolíme přepnutí, pokud ještě NEBYLO odemknuto
        if ($alreadyChosen && $alreadyChosen->getId() !== $offer->getId()) {
            // Je na původní nabídce aktivní unlock (zaplaceno)? Pak změnu nepovolíme.
            $activeUnlock = $this->unlocks->findOneBy([
                'offer'    => $alreadyChosen,
                'customer' => $user,
                'status'   => OfferUnlock::STATUS_ACTIVE,
            ]);
            if ($activeUnlock) {
                $this->addFlash('warning', 'Kontakt je již odemčen. Změna výběru není možná.');
                return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
            }

            // Zruš pending unlock + pending platby k původní nabídce
            $pendingUnlock = $this->unlocks->findOneBy([
                'offer'    => $alreadyChosen,
                'customer' => $user,
                'status'   => OfferUnlock::STATUS_PENDING,
            ]);
            if ($pendingUnlock) {
                $pendingPayments = $this->payments->findBy([
                    'unlock' => $pendingUnlock,
                    'status' => Payment::STATUS_PENDING,
                ]);
                foreach ($pendingPayments as $p) {
                    $p->setStatus(Payment::STATUS_CANCELLED);
                    $this->payments->add($p, false); // bez flush
                }
                $pendingUnlock->setStatus(OfferUnlock::STATUS_EXPIRED);
                $pendingUnlock->setExpiresAt(new \DateTimeImmutable());
                $this->unlocks->add($pendingUnlock, false);
            }

            // Přepni na novou nabídku (stav necháme 'selected')
            $quote->setChosenOffer($offer);
            if ($quote->getStatus() !== 'selected') {
                $quote->setStatus('selected');
            }
            $this->quotes->add($quote, true);

            $this->addFlash('success', 'Změnil jsi vybranou nabídku.');
            return $this->redirectToRoute('app_web_unlock_pay', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        // Poprvé vybíráme
        $quote->setChosenOffer($offer);
        $quote->setStatus('selected');
        $this->quotes->add($quote, true);

        $this->addFlash('success', 'Vybral jsi nabídku.');
        return $this->redirectToRoute('app_web_unlock_pay', ['id' => $id, '_locale' => $request->getLocale()]);
    }

    #[Route('moje-poptavky', name: 'my_quotes', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myQuotes(Request $request): Response
    {
        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        $status = $request->query->get('status'); // optional: open/selected/closed/cancelled

        $qb = $this->quotes->createQueryBuilder('q')
            ->andWhere('q.user = :u')->setParameter('u', $user)
            ->orderBy('q.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('q.status = :s')->setParameter('s', $status);
        }

        $quotes = $qb->getQuery()->getResult();

        // počty dle stavu (pro filtry)
        $countsRaw = $this->quotes->getEntityManager()->createQuery(
            'SELECT q.status AS s, COUNT(q.id) AS c FROM App\Entity\Web\Quote q WHERE q.user = :u GROUP BY q.status'
        )->setParameter('u', $user)->getResult();

        $counts = ['open'=>0, 'selected'=>0, 'closed'=>0, 'cancelled'=>0];
        foreach ($countsRaw as $row) { $counts[$row['s']] = (int)$row['c']; }

        return $this->render('web/account/my_quotes.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService'  => $this->projectService->getProject(),
            'quotes'          => $quotes,
            'counts'          => $counts,
            'activeStatus'    => $status,
        ]);
    }

    #[Route('poptavka/{id}/zrusit-vyber', name: 'quote_unselect_offer', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function unselectOffer(Request $request, int $id): Response
    {
        $quote = $this->quotes->find($id);
        if (!$quote) { throw $this->createNotFoundException('Poptávka nenalezena.'); }

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        if ($quote->getUser()->getId() !== $user->getId()) {
            $this->addFlash('danger', 'Nemáš oprávnění pro tuto akci.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        if (!$this->isCsrfTokenValid('unselect_quote_'.$quote->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Neplatný CSRF token.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        if ($quote->getStatus() !== 'selected' || !$quote->getChosenOffer()) {
            $this->addFlash('info', 'U této poptávky není žádná vybraná nabídka.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        $chosen = $quote->getChosenOffer();

        // Pokud už je aktivní unlock (zaplaceno), dál to nepovolíme
        $activeUnlock = $this->unlocks->findOneBy([
            'offer'    => $chosen,
            'customer' => $user,
            'status'   => OfferUnlock::STATUS_ACTIVE,
        ]);
        if ($activeUnlock) {
            $this->addFlash('warning', 'Kontakt už je odemčen – zrušení výběru není možné.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        // Zruš pending unlock + pending payments, pokud existují
        $pendingUnlock = $this->unlocks->findOneBy([
            'offer'    => $chosen,
            'customer' => $user,
            'status'   => OfferUnlock::STATUS_PENDING,
        ]);
        if ($pendingUnlock) {
            $pendingPayments = $this->payments->findBy([
                'unlock' => $pendingUnlock,
                'status' => Payment::STATUS_PENDING,
            ]);
            foreach ($pendingPayments as $p) {
                $p->setStatus(Payment::STATUS_CANCELLED);
                $this->payments->add($p, false);
            }
            $pendingUnlock->setStatus(OfferUnlock::STATUS_EXPIRED);
            $pendingUnlock->setExpiresAt(new \DateTimeImmutable());
            $this->unlocks->add($pendingUnlock, false);
        }

        // Vrátit do 'open'
        $quote->setChosenOffer(null);
        $quote->setStatus('open');
        $this->quotes->add($quote, true);

        $this->addFlash('success', 'Výběr nabídky byl zrušen. Můžeš vybrat jinou.');
        return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
    }
}
