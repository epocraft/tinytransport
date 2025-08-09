<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderOpeningException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceProviderOpeningExceptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProviderOpeningException::class); }

    public function add(ServiceProviderOpeningException $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceProviderOpeningException $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return ServiceProviderOpeningException[] */
    public function findForProviderBetween(ServiceProvider $sp, \DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.serviceProvider = :sp')->setParameter('sp', $sp)
            ->andWhere('e.date BETWEEN :f AND :t')->setParameter('f', $from)->setParameter('t', $to)
            ->orderBy('e.date', 'ASC')->getQuery()->getResult();
    }
}
