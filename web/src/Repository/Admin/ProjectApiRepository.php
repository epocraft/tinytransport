<?php

namespace App\Repository\Admin;

use App\Entity\Admin\ProjectApi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectApi>
 *
 * @method ProjectApi|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectApi|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectApi[]    findAll()
 * @method ProjectApi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectApiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectApi::class);
    }

    //    /**
    //     * @return ProjectApi[] Returns an array of ProjectApi objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ProjectApi
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
