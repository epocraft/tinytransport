<?php

namespace App\Controller\Web;

use App\Entity\Web\Article;
use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FurnitureTransportController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private EntityManagerInterface $entityManager,
    ) {}

    #[Route('/{_locale<%app.supported_locales%>}/preprava-nabytku', name: 'app_web_furniture_transport')]
    public function index(Request $request): Response
    {
        $article = $this->entityManager->getRepository(Article::class)->findOneBy([
            'urlAlias' => 'preprava-nabytku',
            'publication' => 1
        ]);

        if (!$article) {
            // Pokud článek s daným urlAliasem neexistuje, hodíme 404 chybu
            throw $this->createNotFoundException('Článek nebyl nalezen.');
        }

        return $this->render('web/furniture_transport/' . $request->getLocale() . '/index.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'article' => $article,
        ]);
    }
}
