<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FileExistsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('fileExists', [$this, 'fileExistsFunction']),
        ];
    }

    public function fileExistsFunction(string $filePath): bool
    {
        return file_exists($filePath);
    }
}