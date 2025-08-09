<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderVehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceProviderVehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProviderVehicle::class); }

    public function add(ServiceProviderVehicle $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceProviderVehicle $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return ServiceProviderVehicle[] */
    public function findForProvider(ServiceProvider $sp, bool $onlyPublished = true): array
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.serviceProvider = :sp')->setParameter('sp', $sp)
            ->orderBy('v.createdAt', 'DESC');
        if ($onlyPublished) { $qb->andWhere('v.publication = 1'); }
        return $qb->getQuery()->getResult();
    }
}
