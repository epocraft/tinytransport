<?php

namespace App\Twig;

use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IsProjectModuleEnabledExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isProjectModuleEnabled', [$this, 'isProjectModuleEnabled']),
        ];
    }

    public function isProjectModuleEnabled(Collection $modules, string $moduleName): bool
    {
        return $modules->exists(function($key, $module) use ($moduleName) {
            return $module->getName() === $moduleName && $module->getPublication();
        });
    }
}
