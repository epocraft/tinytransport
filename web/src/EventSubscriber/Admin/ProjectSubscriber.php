<?php

namespace App\EventSubscriber\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectSubscriber implements EventSubscriberInterface
{
    
    public function __construct(
        private UrlGeneratorInterface $router,
        private Security $security,
    ) {
        
    }

    public static function getSubscribedEvents()
    {
        // Nastavíme vyšší prioritu, aby se tento posluchač spustil před RouterListenerem.
        return [
            'kernel.request' => ['onKernelRequest', 100],
        ];
    }

    /**
     * Handles the kernel request event.
     * Pokud není nastavený žádný projekt, je uživatel přesměrován na dashboard
     * Z tohoto jsou vyjmuty cesty $excludedRoutes
     *
     * @param RequestEvent $requestEvent The event to handle.
     */
    public function onKernelRequest(RequestEvent $requestEvent) {

        /** @var Request $request */
        $request = $requestEvent->getRequest();

        if (strpos($request->getPathInfo(), '/admin') === 0) {

            // admin
            $session = $request->getSession();

            $user = $this->security->getUser();
        
            // Cesty, které nevyžadují zvolený projekt
            $excludedRoutes = ['app_admin_dashboard', 'app_login'];

            if ($user) {
                $currentRoute = $request->attributes->get('_route');

                if (!in_array($currentRoute, $excludedRoutes, true)) {
                    if (!$session->has('project')) {
                        //$requestEvent->setResponse(new RedirectResponse($this->router->generate('app_admin_dashboard')));
                    }
                }
            }

        } else {

            // no admin

        }

    }

    public function onKernelResponse(ResponseEvent $event)
    {
        
    }
}