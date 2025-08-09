<?php

namespace App\Service\Admin;

use App\Entity\Admin\Project;
use App\Repository\Admin\LanguageRepository;
use App\Repository\Admin\ProjectRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private LanguageRepository $languageRepository,
        private RequestStack $requestStack,
    ) {
        
    }
    
    /**
     * getProject
     * 
     * @return Project|null
     */
    public function getProject(): ?Project
    {
        $request = $this->requestStack->getCurrentRequest();
        $projectId = $this->requestStack->getSession()->get('project');

        if ($request) {
            $language = $this->languageRepository->findOneBy(['urlAlias' => $request->getLocale()]);
            if ($language) {
                return $this->projectRepository->getProject($projectId, $language->getId());
            }
        }

        return null;
    }
}
