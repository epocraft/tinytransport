<?php

namespace App\EventSubscriber\Web;

use App\Service\Web\LanguageService;
use App\Service\Web\ProjectService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LanguageService $languageService,
        private ProjectService $projectService,
        private Environment $twig,
    ) {
        $this->projectService = $projectService;
        $this->languageService = $languageService;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(
        RequestEvent $event,
    ) {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Vynechání administrativních rout
        if (strpos($path, '/admin') === 0) {
            return;
        }

        if ($this->projectService->getProject()->getMaintenance() === 1) {

            $content = $this->twig->render('web/maintenance/index.html.twig', [
                'maintenanceText' => $this->projectService->getProject()->getMaintenanceText(),
                'languageService' => $this->languageService->getLanguage(),
                'projectService' => $this->projectService->getProject(),
            ]);
            $response = new Response($content);
            $response->setStatusCode(Response::HTTP_SERVICE_UNAVAILABLE);
            $event->setResponse($response);

        }
    }

    public function onKernelResponse(
        ResponseEvent $event,
    ) {
        
    }
}
