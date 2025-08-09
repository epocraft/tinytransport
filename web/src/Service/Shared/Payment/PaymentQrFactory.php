<?php

namespace App\Service\Shared\Payment;

use App\Entity\Web\Payment;

/**
 * Generuje payload pro CZ QR platbu (SPAYD / "SPD*1.0").
 * Kompatibilní s bankovními aplikacemi v ČR.
 *
 * Pozn.: ACC může být IBAN (doporučeno) nebo "čísloú/КБ" (např. 123456789/0100).
 * V projektu můžeš čerpat IBAN z tabulky `project` (sloupec ci_iban) a předat ho sem.
 */
class PaymentQrFactory
{
    public function buildSpaydPayload(Payment $p, ?string $ibanOrAccount = null): string
    {
        $acc = $ibanOrAccount ?: $p->getBankAccount(); // očekává se IBAN nebo "123456789/0100"

        $parts = [
            'SPD*1.0',
            'ACC:' . $this->sanitize($acc),
            'AM:' . $this->formatAmount($p->getAmountTotal()),
            'CC:' . $p->getCurrency(),
            'X-VS:' . $this->sanitize($p->getVs()),
        ];

        if ($p->getMessage()) {
            $parts[] = 'MSG:' . $this->truncate($p->getMessage(), 60);
        }
        if ($p->getBankAccountName()) {
            $parts[] = 'RN:' . $this->truncate($p->getBankAccountName(), 35);
        }

        // Volitelně datum splatnosti: $parts[] = 'DT:' . (new \DateTimeImmutable())->format('Ymd');

        return implode('*', $parts);
    }

    /** Naplní Payment->qrPayload hotovým SPD řetězcem a vrátí ho. */
    public function attachToPayment(Payment $p, ?string $ibanOrAccount = null): Payment
    {
        $p->setQrPayload($this->buildSpaydPayload($p, $ibanOrAccount));
        return $p;
    }

    private function sanitize(string $s): string
    {
        // vyhoď CRLF a hvězdičky (oddělovače)
        $s = str_replace(["\r", "\n", '*'], '', $s);
        return trim($s);
    }

    private function formatAmount(string $decimal): string
    {
        // SPD očekává tečku jako oddělovač; Payment používá string (DECIMAL), tak jen normalize
        return str_replace(',', '.', $decimal);
    }

    private function truncate(string $s, int $len): string
    {
        return mb_strlen($s) > $len ? mb_substr($s, 0, $len) : $s;
    }
}
