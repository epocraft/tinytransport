<?php

namespace App\EventSubscriber\Admin;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(private Security $security)
    {
        
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelRequest(RequestEvent $requestEvent)
    {
        $user = $this->security->getUser();

        if ($user) {
            // Access in controller: $user = $request->attributes->get('user');
            // Access in template (Twig): {{ app.request.attributes.get('user') }}
            $requestEvent->getRequest()->attributes->set('user', $user);
        }
    }

    public function onKernelResponse(ResponseEvent $event) {
        
    }
}
