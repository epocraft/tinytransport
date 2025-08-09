<?php

namespace App\Repository\Admin;

use App\Entity\Admin\ProjectText;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectText>
 *
 * @method ProjectText|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectText|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectText[]    findAll()
 * @method ProjectText[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectTextRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectText::class);
    }

    //    /**
    //     * @return ProjectText[] Returns an array of ProjectText objects
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

    //    public function findOneBySomeField($value): ?ProjectText
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
