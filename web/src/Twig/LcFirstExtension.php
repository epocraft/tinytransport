<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class LcFirstExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('lcfirst', [$this, 'lcFirstFunction']),
        ];
    }

    public function lcFirstFunction(string $string): string
    {
        // Implementace UTF-8 podpory pro lcfirst
        if (function_exists('mb_strtolower') && function_exists('mb_substr') && !empty($string)) {
            $firstChar = mb_strtolower(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8');
            $rest = mb_substr($string, 1, null, 'UTF-8');
            return $firstChar . $rest;
        }

        // Záložní možnost bez UTF-8 podpory
        return lcfirst($string);
    }
}