<?php

namespace App\Service\Admin;

use App\Entity\Admin\Language;
use App\Repository\Admin\LanguageRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class LanguageService
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private RequestStack $requestStack,
    ) {
        
    }
    
    /**
     * getLanguage
     * 
     * @return Language|null
     */
    public function getLanguage(): ?Language
    {
        $request = $this->requestStack->getCurrentRequest();
        
        if ($request) {

            return $this->languageRepository->getLanguage($request->getLocale());
            
        }

        return null;
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
