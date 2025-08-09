<?php

namespace App\Service\Web;

use App\Entity\Web\Language;
use App\Repository\Web\LanguageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class LanguageService
{
    private $languageRepository;
    private $requestStack;

    public function __construct(
        LanguageRepository $languageRepository,
        RequestStack $requestStack,
    )
    {
        $this->languageRepository = $languageRepository;
        $this->requestStack = $requestStack;
    }
    
    /**
     * getProject
     *
     * předpoklad je, že project id je vždy 1
     * 
     * @return Language|null
     */
    public function getLanguage(): ?Language
    {
        $request = $this->requestStack->getCurrentRequest();

        return $this->languageRepository->getLanguage($request->getLocale());
    }

    /**
     * Get all languages.
     *
     * @return Language[]
     */
    public function getLanguages(): array
    {
        return $this->languageRepository->getLanguages();
    }
}
