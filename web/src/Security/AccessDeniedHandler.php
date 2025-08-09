<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private RouterInterface $router) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): RedirectResponse
    {
        // Přesměrování na vlastní stránku /access-denied
        return new RedirectResponse($this->router->generate('app_admin_access_denied'));
    }
}
