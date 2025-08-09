<?php

namespace App\Repository\Web;

use App\Entity\Web\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, User::class); }

    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')->setParameter('email', $email)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @return User[]
     */
    public function search(string $q, int $limit = 20): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email LIKE :q')->setParameter('q', '%'.$q.'%')
            ->setMaxResults($limit)->getQuery()->getResult();
    }
}
