<?php

namespace App\Repository\Web;

use App\Entity\Web\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Category::class); }

    public function add(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    /**
     * @return Category[]
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.publication = 1')
            ->orderBy('c.name', 'ASC')
            ->getQuery()->getResult();
    }

    public function findOneByName(string $name): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.name = :n')->setParameter('n', $name)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array<int,string> id => name
     */
    public function getIdNameMap(): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('c.id, c.name')
            ->orderBy('c.name', 'ASC')
            ->getQuery()->getArrayResult();

        $map = [];
        foreach ($rows as $r) { $map[(int)$r['id']] = (string)$r['name']; }
        return $map;
    }
}
