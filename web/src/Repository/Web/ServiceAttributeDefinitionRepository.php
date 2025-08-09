<?php

namespace App\Repository\Web;

use App\Entity\Web\Category;
use App\Entity\Web\ServiceAttributeDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceAttributeDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceAttributeDefinition::class); }

    public function add(ServiceAttributeDefinition $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceAttributeDefinition $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return ServiceAttributeDefinition[] */
    public function findByCategory(Category $c): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.category = :c')->setParameter('c', $c)
            ->orderBy('d.code', 'ASC')->getQuery()->getResult();
    }

    public function findOneByCategoryAndCode(Category $c, string $code): ?ServiceAttributeDefinition
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.category = :c')->setParameter('c', $c)
            ->andWhere('d.code = :code')->setParameter('code', $code)
            ->getQuery()->getOneOrNullResult();
    }
}
