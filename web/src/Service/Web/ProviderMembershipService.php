<?php

namespace App\Service\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderUser;
use App\Entity\Web\User;
use App\Repository\Web\ServiceProviderUserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProviderMembershipService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ServiceProviderUserRepository $spuRepo,
    ) {}

    /**
     * Propojí uživatele s providerem (pokud už není) a zajistí ROLE_PROVIDER.
     */
    public function attachManager(User $user, ServiceProvider $provider, bool $flush = true): ServiceProviderUser
    {
        $link = $this->spuRepo->findOneBy(['user' => $user, 'serviceProvider' => $provider]);
        if (!$link) {
            $link = (new ServiceProviderUser())
                ->setUser($user)
                ->setServiceProvider($provider);
                // ->setRole(ServiceProviderUser::ROLE_OWNER) // pokud to ve tvé entitě máš
            $this->em->persist($link);
            if ($flush) { $this->em->flush(); }
        }

        $this->syncProviderRole($user, $flush);
        return $link;
    }

    /**
     * Zruší propojení a případně sundá ROLE_PROVIDER (pokud už žádné jiné propojení neexistuje).
     */
    public function detachManager(User $user, ServiceProvider $provider, bool $flush = true): void
    {
        $link = $this->spuRepo->findOneBy(['user' => $user, 'serviceProvider' => $provider]);
        if ($link) {
            $this->em->remove($link);
            if ($flush) { $this->em->flush(); }
        }
        $this->syncProviderRole($user, $flush);
    }

    /**
     * Přidá/odebere ROLE_PROVIDER podle počtu propojení v service_provider_user.
     */
    public function syncProviderRole(User $user, bool $flush = true): void
    {
        $hasAny = $this->spuRepo->countByUser($user) > 0;
        if ($hasAny) {
            $user->addRole('ROLE_PROVIDER');
        } else {
            $user->removeRole('ROLE_PROVIDER');
        }
        $this->em->persist($user);
        if ($flush) { $this->em->flush(); }
    }
}
