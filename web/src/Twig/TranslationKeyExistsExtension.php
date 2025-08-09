<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Contracts\Translation\TranslatorInterface;

class TranslationKeyExistsExtension extends AbstractExtension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('translation_key_exists', [$this, 'translationKeyExists']),
        ];
    }

    public function translationKeyExists(string $key, string $domain = 'messages'): bool
    {
        // Přeloží klíč a porovná, zda přeložená hodnota je stejná jako vstupní klíč
        return $this->translator->trans($key, [], $domain) !== $key;
    }
}
