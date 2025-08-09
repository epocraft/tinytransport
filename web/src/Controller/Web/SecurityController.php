<?php

namespace App\Controller\Web;

use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {}

    #[Route(path: '/')]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('app_web_login');
    }

    #[Route('/access-denied', name: 'app_web_access_denied', options: ['expose' => true])]
    public function accessDenied(): Response
    {
        return $this->render('web/security/error403.html.twig', [
            'page' => $this->translator->trans('breadcrumbs.access_denied', [], 'security'),
            'pageTitle' => $this->translator->trans('breadcrumbs.access_denied', [], 'security'),
        ]);
    }

    #[Route(path: '/login', name: 'app_web_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        TranslatorInterface $translatorInterface,
        Request $request,
    ): Response {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('web/security/login.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'page' => $translatorInterface->trans('breadcrumbs.login', [], 'security'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.login', [], 'security'),
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_web_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/logged-out', name: 'app_web_logged_out')]
    public function loggedOut(TranslatorInterface $translatorInterface,): Response
    {
        return $this->render('web/security/logged_out.html.twig', [
            'languageService' => $this->languageService->getLanguage(),
            'projectService' => $this->projectService->getProject(),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.successful_logout', [], 'security'),
        ]);
    }
}
