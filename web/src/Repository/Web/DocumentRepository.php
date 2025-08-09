<?php

namespace App\Repository\Web;

use App\Entity\Web\Document;
use App\Entity\Web\EntityRef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Document::class); }

    public function add(Document $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(Document $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return Document[] */
    public function findByEntityAndRecord(EntityRef $entity, int $recordId): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.entity = :e')->setParameter('e', $entity)
            ->andWhere('d.recordId = :r')->setParameter('r', $recordId)
            ->orderBy('d.position', 'ASC')->addOrderBy('d.id', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return Document[] */
    public function findByEntityNameAndRecord(string $entityName, int $recordId): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.entity', 'er')
            ->andWhere('er.name = :n')->setParameter('n', $entityName)
            ->andWhere('d.recordId = :r')->setParameter('r', $recordId)
            ->orderBy('d.position', 'ASC')->addOrderBy('d.id', 'ASC')
            ->getQuery()->getResult();
    }
}
