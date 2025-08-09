<?php

namespace App\Controller\Web;

use App\Entity\Web\Offer;
use App\Entity\Web\Quote;
use App\Form\Web\OfferType;
use App\Repository\Web\OfferRepository;
use App\Repository\Web\QuoteRepository;
use App\Repository\Web\ServiceProviderUserRepository;
use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale<%app.supported_locales%>}/', name: 'app_web_')]
class OfferController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private readonly OfferRepository $offers,
        private readonly QuoteRepository $quotes,
        private readonly ServiceProviderUserRepository $spUsers
    ) {}

    #[Route('poptavka/{id}/nabidka', name: 'offer_new', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, int $id): Response
    {
        $quote = $this->quotes->find($id);
        if (!$quote) {
            throw $this->createNotFoundException('Poptávka nenalezena.');
        }
        if ($quote->getStatus() !== 'open') {
            $this->addFlash('warning', 'Na tuto poptávku už nelze podat nabídku.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();

        // 👉 Zákazník nemůže nabízet na vlastní poptávku
        if ($quote->getUser()->getId() === $user->getId()) {
            $this->addFlash('warning', 'Na vlastní poptávku nemůžeš podávat nabídku.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }

        // Vyber poskytovatele, kterého uživatel spravuje
        $links = $this->spUsers->findProvidersByUser($user); // ServiceProviderUser[]
        $providers = array_map(fn($link) => $link->getServiceProvider(), $links);

        if (count($providers) === 0) {
            $this->addFlash('warning', 'Nemáš přiřazen žádný profil poskytovatele. Nejdřív si ho vytvoř.');
            return $this->redirectToRoute('app_web_search', ['_locale' => $request->getLocale()]);
        }

        // Pokud jich je víc, vybereme přes ?sp=ID nebo ukážeme výběr
        $spId = $request->query->getInt('sp', 0);
        $provider = null;

        if (count($providers) === 1) {
            $provider = $providers[0];
        } else {
            if ($spId > 0) {
                foreach ($providers as $p) {
                    if ($p->getId() === $spId) { $provider = $p; break; }
                }
                if (!$provider) {
                    $this->addFlash('warning', 'Nemáš oprávnění k vybranému poskytovateli.');
                    return $this->redirectToRoute('app_web_offer_select_provider', ['id' => $id, '_locale' => $request->getLocale()]);
                }
            } else {
                return $this->render('web/offer/select_provider.html.twig', [
                    'languageService' => $this->languageService->getLanguage(),
                    'projectService' => $this->projectService->getProject(),
                    'quote' => $quote,
                    'providers' => $providers,
                ]);
            }
        }

        $offer = new Offer();
        $offer->setQuote($quote);
        $offer->setServiceProvider($provider);
        $offer->setCurrency('CZK'); // default

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // DB má UNIQUE (service_provider_id, quote_id), takže opakování se samo zablokuje
            $this->offers->add($offer, true);
            $this->addFlash('success', 'Nabídka byla odeslána.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $quote->getId(), '_locale' => $request->getLocale()]);
        }

        return $this->render('web/offer/new.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'quote' => $quote,
            'form'  => $form,
            'provider' => $provider,
        ]);
    }

    #[Route('nabidka/{id}/upravit', name: 'offer_edit', requirements: ['id' => '\d+'], methods: ['GET','POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function edit(Request $request, int $id): Response
    {
        $offer = $this->offers->find($id);
        if (!$offer) {
            throw $this->createNotFoundException('Nabídka nenalezena.');
        }

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();

        // Autorizace: uživatel musí spravovat poskytovatele, který nabídku podal
        $links = $this->spUsers->findProvidersByUser($user);
        $managedIds = array_map(fn($l) => $l->getServiceProvider()->getId(), $links);
        if (!in_array($offer->getServiceProvider()->getId(), $managedIds, true)) {
            $this->addFlash('danger', 'Nemáš oprávnění upravovat tuto nabídku.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $offer->getQuote()->getId(), '_locale' => $request->getLocale()]);
        }

        // Lze upravovat jen aktivní nabídku na otevřenou poptávku
        if ($offer->getStatus() !== Offer::STATUS_ACTIVE || $offer->getQuote()->getStatus() !== Quote::STATUS_OPEN) {
            $this->addFlash('warning', 'Tuto nabídku už nelze upravit.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $offer->getQuote()->getId(), '_locale' => $request->getLocale()]);
        }

        // 🔒 THROTTLE: min. 5 hodin od poslední editace (nebo od vytvoření)
        $last = $offer->getLastEditAt() ?? $offer->getCreatedAt();
        $nextAllowed = (clone $last)->modify('+5 hours');
        $now = new \DateTimeImmutable();
        if ($now < $nextAllowed) {
            $diff = $now->diff($nextAllowed);
            $hours = $diff->h + $diff->d * 24;
            $mins  = $diff->i;
            $this->addFlash('warning', sprintf('Nabídku lze upravit za %d h %02d min.', $hours, $mins));
            return $this->redirectToRoute('app_web_quote_show', ['id' => $offer->getQuote()->getId(), '_locale' => $request->getLocale()]);
        }

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $offer->setLastEditAt(new \DateTimeImmutable());
            // updated_at se ti v DB doplní samo díky ON UPDATE CURRENT_TIMESTAMP
            $this->offers->add($offer, true);
            $this->addFlash('success', 'Nabídka byla upravena.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $offer->getQuote()->getId(), '_locale' => $request->getLocale()]);
        }

        return $this->render('web/offer/edit.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService'  => $this->projectService->getProject(),
            'offer'           => $offer,
            'form'            => $form,
        ]);
    }

    #[Route('nabidka/{id}/stahnout', name: 'offer_withdraw', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function withdraw(Request $request, int $id): Response
    {
        $offer = $this->offers->find($id);
        if (!$offer) {
            throw $this->createNotFoundException('Nabídka nenalezena.');
        }

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();

        // Uživatel musí být správcem poskytovatele, který nabídku podal
        $links = $this->spUsers->findProvidersByUser($user);
        $managedIds = array_map(fn($l) => $l->getServiceProvider()->getId(), $links);
        if (!in_array($offer->getServiceProvider()->getId(), $managedIds, true)) {
            $this->addFlash('danger', 'Nemáš oprávnění stáhnout tuto nabídku.');
            return $this->redirectToRoute('app_web_quote_show', [
                'id' => $offer->getQuote()->getId(),
                '_locale' => $request->getLocale()
            ]);
        }

        // Stáhnout lze jen aktivní nabídku
        if ($offer->getStatus() !== Offer::STATUS_ACTIVE) {
            $this->addFlash('warning', 'Tuto nabídku již nelze stáhnout.');
            return $this->redirectToRoute('app_web_quote_show', [
                'id' => $offer->getQuote()->getId(),
                '_locale' => $request->getLocale()
            ]);
        }

        $this->offers->withdraw($offer, true);
        $this->addFlash('success', 'Nabídka byla stažena.');
        return $this->redirectToRoute('app_web_quote_show', [
            'id' => $offer->getQuote()->getId(),
            '_locale' => $request->getLocale()
        ]);
    }

    #[Route('moje-nabidky', name: 'my_offers', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function myOffers(Request $request): Response
    {
        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        $status = $request->query->get('status'); // active/accepted/rejected/withdrawn/expired

        // zjisti, které poskytovatele uživatel spravuje
        $links = $this->spUsers->findProvidersByUser($user);
        $providerIds = array_map(fn($l) => $l->getServiceProvider()->getId(), $links);

        if (empty($providerIds)) {
            $this->addFlash('info', 'Nemáš zatím přiřazen žádný profil poskytovatele.');
            return $this->render('web/account/my_offers.html.twig', [
                'languageService' => $this->languageService->getLanguage(),
                'projectService'  => $this->projectService->getProject(),
                'offers'          => [],
                'counts'          => [],
                'activeStatus'    => $status,
            ]);
        }

        $qb = $this->offers->createQueryBuilder('o')
            ->andWhere('o.serviceProvider IN (:pids)')->setParameter('pids', $providerIds)
            ->orderBy('o.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('o.status = :s')->setParameter('s', $status);
        }

        $offers = $qb->getQuery()->getResult();

        // počty dle stavu
        $countsRaw = $this->offers->getEntityManager()->createQuery(
            'SELECT o.status AS s, COUNT(o.id) AS c FROM App\Entity\Web\Offer o WHERE o.serviceProvider IN (:pids) GROUP BY o.status'
        )->setParameter('pids', $providerIds)->getResult();

        $counts = [];
        foreach ($countsRaw as $row) { $counts[$row['s']] = (int)$row['c']; }

        return $this->render('web/account/my_offers.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService'  => $this->projectService->getProject(),
            'offers'          => $offers,
            'counts'          => $counts,
            'activeStatus'    => $status,
        ]);
    }
}
