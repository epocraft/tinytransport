<?php

namespace App\Repository\Admin;

use App\Entity\Admin\ProjectContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProjectContact>
 *
 * @method ProjectContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectContact[]    findAll()
 * @method ProjectContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectContact::class);
    }

//    /**
//     * @return ProjectContact[] Returns an array of ProjectContact objects
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

//    public function findOneBySomeField($value): ?ProjectContact
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}