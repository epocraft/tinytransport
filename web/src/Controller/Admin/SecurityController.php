<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/admin')]
class SecurityController extends AbstractController
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {}

    #[Route(path: '/')]
    public function indexNoLocale(): Response
    {
        return $this->redirectToRoute('app_admin_login');
    }

    #[Route('/access-denied', name: 'app_admin_access_denied', options: ['expose' => true])]
    public function accessDenied(): Response
    {
        return $this->render('admin/security/error403.html.twig', [
            'page' => $this->translator->trans('breadcrumbs.access_denied', [], 'security'),
            'pageTitle' => $this->translator->trans('breadcrumbs.access_denied', [], 'security'),
        ]);
    }

    #[Route(path: '/login', name: 'app_admin_login')]
    public function login(
        AuthenticationUtils $authenticationUtils,
        TranslatorInterface $translatorInterface,
        Request $request,
    ): Response {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/security/login.html.twig', [
            'page' => $translatorInterface->trans('breadcrumbs.login', [], 'security'),
            'pageTitle' => $translatorInterface->trans('breadcrumbs.login', [], 'security'),
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_admin_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/logged-out', name: 'app_admin_logged_out')]
    public function loggedOut(TranslatorInterface $translatorInterface,): Response
    {
        return $this->render('admin/security/logged-out.html.twig', [
            'pageTitle' => $translatorInterface->trans('breadcrumbs.successful_logout', [], 'security'),
        ]);
    }

    #[Route('/logout-project', name: 'app_admin_logout_project', methods: ['GET', 'POST'])]
    public function logoutProject(
    ): Response {
        $session = $this->requestStack->getSession();

        $session->remove('project');

        return $this->redirectToRoute('app_admin_dashboard', [], Response::HTTP_SEE_OTHER);
    }
}
