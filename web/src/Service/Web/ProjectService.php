<?php

namespace App\Service\Web;

use App\Entity\Web\Project;
use App\Repository\Web\LanguageRepository;
use App\Repository\Web\ProjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectService
{
    private $projectRepository;
    private $languageRepository;
    private $requestStack;

    public function __construct(
        ProjectRepository $projectRepository,
        LanguageRepository $languageRepository,
        RequestStack $requestStack,
    )
    {
        $this->projectRepository = $projectRepository;
        $this->languageRepository = $languageRepository;
        $this->requestStack = $requestStack;
    }
    
    /**
     * getProject
     *
     * předpoklad je, že project id je vždy 1
     * 
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        $request = $this->requestStack->getCurrentRequest();
        
        $language = $this->languageRepository->findOneBy(['urlAlias' => $request->getLocale()]);

        return $this->projectRepository->getProject(1, $language->getId());
    }
}
