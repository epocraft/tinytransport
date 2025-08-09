<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceProviderAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProviderAddress::class); }

    public function add(ServiceProviderAddress $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceProviderAddress $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    public function findPrimary(ServiceProvider $sp): ?ServiceProviderAddress
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.serviceProvider = :sp')->setParameter('sp', $sp)
            ->addOrderBy('a.isPrimary', 'DESC')
            ->addOrderBy('a.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /** Zajistí, že adresa $addr bude jediná primární. */
    public function setPrimary(ServiceProviderAddress $addr, bool $flush = true): void
    {
        $em = $this->getEntityManager();
        $sp = $addr->getServiceProvider();

        $em->wrapInTransaction(function() use ($em, $sp, $addr) {
            $em->createQuery('UPDATE App\Entity\Web\ServiceProviderAddress a SET a.isPrimary = 0 WHERE a.serviceProvider = :sp')
                ->setParameter('sp', $sp)->execute();

            $addr->setIsPrimary(true);
            $em->persist($addr);
        });

        if ($flush) { $em->flush(); }
    }

    /** @return ServiceProviderAddress[] */
    public function findAllForProvider(ServiceProvider $sp): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.serviceProvider = :sp')->setParameter('sp', $sp)
            ->orderBy('a.isPrimary', 'DESC')->addOrderBy('a.id', 'ASC')
            ->getQuery()->getResult();
    }
}
