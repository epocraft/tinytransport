<?php

namespace App\Repository\Web;

use App\Entity\Web\EntityRef;
use App\Entity\Web\Image;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Image::class); }

    public function add(Image $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(Image $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return Image[] */
    public function findByEntityAndRecord(EntityRef $entity, int $recordId): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.entity = :e')->setParameter('e', $entity)
            ->andWhere('i.recordId = :r')->setParameter('r', $recordId)
            ->orderBy('i.position', 'ASC')->addOrderBy('i.id', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return Image[] */
    public function findByEntityNameAndRecord(string $entityName, int $recordId): array
    {
        return $this->createQueryBuilder('i')
            ->join('i.entity', 'er')
            ->andWhere('er.name = :n')->setParameter('n', $entityName)
            ->andWhere('i.recordId = :r')->setParameter('r', $recordId)
            ->orderBy('i.position', 'ASC')->addOrderBy('i.id', 'ASC')
            ->getQuery()->getResult();
    }
}
