<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderOpeningHours;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceProviderOpeningHoursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProviderOpeningHours::class); }

    public function add(ServiceProviderOpeningHours $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceProviderOpeningHours $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return ServiceProviderOpeningHours[] */
    public function findForProviderOrdered(ServiceProvider $sp): array
    {
        return $this->createQueryBuilder('h')
            ->andWhere('h.serviceProvider = :sp')->setParameter('sp', $sp)
            ->orderBy('h.dayOfWeek', 'ASC')->getQuery()->getResult();
    }
}
