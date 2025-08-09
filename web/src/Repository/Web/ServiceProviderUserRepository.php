<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderUser;
use App\Entity\Web\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceProviderUser>
 */
class ServiceProviderUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProviderUser::class); }

    public function add(ServiceProviderUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function remove(ServiceProviderUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    /**
     * @return ServiceProviderUser[]
     */
    public function findManagersByProvider(ServiceProvider $provider): array
    {
        return $this->createQueryBuilder('link')
            ->andWhere('link.serviceProvider = :sp')->setParameter('sp', $provider)
            ->getQuery()->getResult();
    }

    /**
     * @return ServiceProviderUser[]
     */
    public function findProvidersByUser(User $user): array
    {
        return $this->createQueryBuilder('link')
            ->andWhere('link.user = :u')->setParameter('u', $user)
            ->leftJoin('link.serviceProvider', 'sp')->addSelect('sp')
            ->getQuery()->getResult();
    }

    public function isUserManagerOfProvider(User $user, ServiceProvider $provider): bool
    {
        $count = $this->createQueryBuilder('link')
            ->select('COUNT(link.user)')
            ->andWhere('link.user = :u')->setParameter('u', $user)
            ->andWhere('link.serviceProvider = :sp')->setParameter('sp', $provider)
            ->getQuery()->getSingleScalarResult();

        return ((int)$count) > 0;
    }

    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('spu')
            ->select('COUNT(spu.id)')
            ->andWhere('spu.user = :u')->setParameter('u', $user)
            ->getQuery()->getSingleScalarResult();
    }

    public function exists(User $user, ServiceProvider $provider): bool
    {
        return (bool) $this->createQueryBuilder('spu')
            ->select('1')
            ->andWhere('spu.user = :u')->setParameter('u', $user)
            ->andWhere('spu.serviceProvider = :p')->setParameter('p', $provider)
            ->getQuery()->getOneOrNullResult();
    }
}
