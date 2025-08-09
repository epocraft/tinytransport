<?php

namespace App\Controller\Web;

use App\Entity\Web\OfferUnlock;
use App\Entity\Web\Payment;
use App\Repository\Web\OfferRepository;
use App\Repository\Web\OfferUnlockRepository;
use App\Repository\Web\PaymentRepository;
use App\Repository\Web\QuoteRepository;
use App\Service\Shared\Payment\PaymentQrFactory; // ✅ správný namespace
use App\Service\Shared\Payment\QrImageRenderer;
use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/{_locale<%app.supported_locales%>}/', name: 'app_web_')]
class UnlockController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private readonly QuoteRepository $quotes,
        private readonly OfferRepository $offers,
        private readonly OfferUnlockRepository $unlocks,
        private readonly PaymentRepository $payments,
        private readonly PaymentQrFactory $qrFactory,
        private readonly QrImageRenderer $qrRenderer
    ) {}

    #[Route('poptavka/{id}/platba', name: 'unlock_pay', requirements: ['id' => '\\d+'], methods: ['GET'])]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
public function pay(Request $request, int $id): Response
{
    $quote = $this->quotes->find($id);
    if (!$quote) { throw $this->createNotFoundException('Poptávka nenalezena.'); }

    /** @var \App\Entity\Web\User $user */
    $user = $this->getUser();

    if ($quote->getUser()->getId() !== $user->getId()) {
        $this->addFlash('danger', 'Nemáš oprávnění k platbě za tuto poptávku.');
        return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
    }

    $offer = $quote->getChosenOffer();
    if (!$offer) {
        $this->addFlash('warning', 'Nejprve vyber nabídku.');
        return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
    }

    // 1) Reuse/reaktivace unlocku pro (offer, customer) — řeší UNIQUE (offer, customer)
    $unlockAny = $this->unlocks->findAnyFor($offer, $user);

    if ($unlockAny && $unlockAny->getStatus() === \App\Entity\Web\OfferUnlock::STATUS_ACTIVE) {
        $this->addFlash('success', 'Kontakt dopravce je již odemčen.');
        return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
    }

    if ($unlockAny && $unlockAny->getStatus() === \App\Entity\Web\OfferUnlock::STATUS_PENDING) {
        $pending = $unlockAny; // pokračuj s pending záznamem
    } else {
        // vytvoř nebo reaktivuj (pokud existoval EXPIRED)
        $now = new \DateTimeImmutable();
        $expires = $now->modify('+48 hours');
        if ($unlockAny) {
            $pending = $this->unlocks->reactivateToPending($unlockAny, $now, $expires, true);
        } else {
            $pending = (new \App\Entity\Web\OfferUnlock())
                ->setOffer($offer)
                ->setCustomer($user)
                ->setStatus(\App\Entity\Web\OfferUnlock::STATUS_PENDING)
                ->setCreatedAt($now)
                ->setExpiresAt($expires);
            $this->unlocks->add($pending, true);
        }
    }

    // 2) Výpočet částek (zatím fixně; později přes konfiguraci)
    $RATE      = 0.10;   // TODO: konfig
    $VAT_RATE  = 0.21;   // TODO: konfig
    $MIN_TOTAL = 99.00;  // TODO: konfig (s DPH)

    $price        = (float) $offer->getPrice();
    $feeBase      = $price * $RATE;                 // bez DPH
    $feeWithVat   = $feeBase * (1.0 + $VAT_RATE);   // s DPH
    $finalTotal   = max($feeWithVat, $MIN_TOTAL);   // min. s DPH

    $amountTotal  = number_format($finalTotal, 2, '.', '');
    $amountBase   = number_format($finalTotal / (1.0 + $VAT_RATE), 2, '.', '');
    $amountVat    = number_format((float)$amountTotal - (float)$amountBase, 2, '.', '');

    // 3) RE-USE platby k tomuto unlocku (vyhnout se duplicitnímu VS)
    $payment = $this->payments->findLatestForUnlock($pending);

    $project = $this->projectService->getProject();
    $iban = method_exists($project, 'getCiIban') ? $project->getCiIban() : null;

    if ($payment) {
        if ($payment->getStatus() === \App\Entity\Web\Payment::STATUS_SETTLED) {
            // settled by znamenalo, že už by unlock měl být ACTIVE → pryč
            $this->addFlash('warning', 'Platba je již uhrazena.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $id, '_locale' => $request->getLocale()]);
        }
        if ($payment->getStatus() === \App\Entity\Web\Payment::STATUS_CANCELLED) {
            // reaktivujeme existující záznam místo vytváření nového
            $payment
                ->setStatus(\App\Entity\Web\Payment::STATUS_PENDING)
                ->setAmountTotal($amountTotal)
                ->setCreatedAt(new \DateTimeImmutable());
            if ($iban && method_exists($payment, 'setBankAccount')) { $payment->setBankAccount($iban); }
            if (method_exists($project, 'getCiName') && $project->getCiName() && method_exists($payment, 'setBankAccountName')) {
                $payment->setBankAccountName($project->getCiName());
            }
            $this->qrFactory->attachToPayment($payment, $iban);
            $this->payments->add($payment, true);
        }
        // pokud je PENDING, jen ho použijeme beze změny
    } else {
        // Vytvoř nový záznam platby (poprvé)
        $payment = (new \App\Entity\Web\Payment())
            ->setUnlock($pending)
            ->setStatus(\App\Entity\Web\Payment::STATUS_PENDING)
            ->setCurrency($offer->getCurrency())
            ->setAmountTotal($amountTotal)
            ->setCreatedAt(new \DateTimeImmutable());

        // VS = zero-pad ID unlocku (unikátní v tabulce)
        $payment->setVs(str_pad((string)$pending->getId(), 10, '0', STR_PAD_LEFT));

        if ($iban && method_exists($payment, 'setBankAccount')) { $payment->setBankAccount($iban); }
        if (method_exists($project, 'getCiName') && $project->getCiName() && method_exists($payment, 'setBankAccountName')) {
            $payment->setBankAccountName($project->getCiName());
        }
        $this->qrFactory->attachToPayment($payment, $iban);
        $this->payments->add($payment, true);
    }

    // 4) QR image
    $qrImageDataUri = $this->qrRenderer->renderDataUri($payment->getQrPayload() ?? '');

    return $this->render('web/payment/pay.html.twig', [
        'languageService' => $this->languageService->getLanguage(),
        'projectService'  => $this->projectService->getProject(),
        'quote'           => $quote,
        'offer'           => $offer,
        'unlock'          => $pending,
        'payment'         => $payment,
        'amountBase'      => $amountBase,
        'amountVat'       => $amountVat,
        'vatRate'         => (int) round($VAT_RATE * 100),
        'ratePercent'     => (int) round($RATE * 100),
        'minTotal'        => number_format($MIN_TOTAL, 2, ',', ' '),
        'qrImageDataUri'  => $qrImageDataUri,
    ]);
}

    #[Route('platba/{id}/oznacit-zaplaceno', name: 'payment_mark_settled', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markSettled(Request $request, int $id): Response
    {
        $payment = $this->payments->find($id);
        if (!$payment) { throw $this->createNotFoundException('Platba nenalezena.'); }

        $unlock = $payment->getUnlock();
        $quote  = $unlock->getOffer()->getQuote();

        /** @var \App\Entity\Web\User $user */
        $user = $this->getUser();
        if ($quote->getUser()->getId() !== $user->getId()) {
            $this->addFlash('danger', 'Nemáš oprávnění dokončit tuto platbu.');
            return $this->redirectToRoute('app_web_quote_show', ['id' => $quote->getId(), '_locale' => $request->getLocale()]);
        }

        $this->payments->markSettled($payment, txId: 'MANUAL-' . $payment->getId(), paidAt: new \DateTimeImmutable(), flush: true);
        $this->unlocks->activate($unlock, new \DateTimeImmutable(), true);

        $this->addFlash('success', 'Platba úspěšně zaevidována. Kontakt dopravce odemčen.');
        return $this->redirectToRoute('app_web_quote_show', ['id' => $quote->getId(), '_locale' => $request->getLocale()]);
    }
}
