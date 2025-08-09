<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashMessagesExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('alert_type', [$this, 'alertType']),
        ];
    }

    public function alertType(string $type): string
    {
        switch ($type) {
            case 'success':
                return 'alert-success';
            case 'error':
                return 'alert-danger';
            case 'warning':
                return 'alert-warning';
            case 'notice':
                return 'alert-info';
            default:
                return 'alert-secondary';
        }
    }
}
