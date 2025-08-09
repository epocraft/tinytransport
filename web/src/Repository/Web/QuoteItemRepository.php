<?php

namespace App\Repository\Web;

use App\Entity\Web\Quote;
use App\Entity\Web\QuoteItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuoteItem>
 */
class QuoteItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, QuoteItem::class); }

    public function add(QuoteItem $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        if ($flush) { $em->flush(); }
    }

    public function remove(QuoteItem $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        if ($flush) { $em->flush(); }
    }

    /** @return QuoteItem[] */
    public function findByQuote(Quote $quote): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.quote = :q')->setParameter('q', $quote)
            ->orderBy('i.id', 'ASC')
            ->getQuery()->getResult();
    }
}
