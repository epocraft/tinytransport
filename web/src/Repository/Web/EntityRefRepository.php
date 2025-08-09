<?php

namespace App\Repository\Web;

use App\Entity\Web\EntityRef;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EntityRefRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, EntityRef::class); }

    public function add(EntityRef $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(EntityRef $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    public function findOneByName(string $name): ?EntityRef
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name = :n')->setParameter('n', $name)
            ->getQuery()->getOneOrNullResult();
    }

    /** Najde nebo vytvoří záznam v `entity` pro daný název. */
    public function getOrCreateByName(string $name, bool $flush = true): EntityRef
    {
        $e = $this->findOneByName($name);
        if ($e) return $e;
        $e = (new EntityRef())->setName($name);
        $this->add($e, $flush);
        return $e;
    }
}
