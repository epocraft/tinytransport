<?php

namespace App\EventListener\Admin;

use App\Entity\Admin\User as AdminUser;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginListener
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {

            $user->setLastLogin(new \DateTimeImmutable());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

        }
    }
}
