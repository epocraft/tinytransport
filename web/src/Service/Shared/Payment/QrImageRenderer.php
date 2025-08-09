<?php

namespace App\Service\Shared\Payment;

class QrImageRenderer
{
    public function renderDataUri(string $payload): ?string
    {
        // fallback: pokud není knihovna, vrať null
        if (!class_exists(\Endroid\QrCode\QrCode::class)) {
            return null;
        }

        $qr = new \Endroid\QrCode\QrCode($payload);
        $qr->setSize(240);

        $writer = new \Endroid\QrCode\Writer\PngWriter();
        $result = $writer->write($qr);

        return $result->getDataUri(); // "data:image/png;base64,..."
    }
}
