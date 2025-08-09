<?php

namespace App\Controller\Web;

use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
    ) {}

    #[Route('/')]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('app_web_homepage', ['_locale' => 'cs']);
    }

    #[Route('/{_locale<%app.supported_locales%>}/', name: 'app_web_homepage')]
    public function index(Request $request): Response {

        return $this->render('web/homepage/' . $request->getLocale() . '/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
        ]);
    }
}
