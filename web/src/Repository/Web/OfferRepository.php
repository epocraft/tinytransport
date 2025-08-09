<?php

namespace App\Repository\Web;

use App\Entity\Web\Offer;
use App\Entity\Web\Quote;
use App\Entity\Web\ServiceProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Offer>
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Offer::class); }

    public function add(Offer $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        if ($flush) { $em->flush(); }
    }

    public function remove(Offer $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        if ($flush) { $em->flush(); }
    }

    /** @return Offer[] */
    public function findActiveByQuote(Quote $quote): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.quote = :q')->setParameter('q', $quote)
            ->andWhere('o.status = :s')->setParameter('s', Offer::STATUS_ACTIVE)
            ->orderBy('o.createdAt', 'ASC')
            ->getQuery()->getResult();
    }

    /** @return Offer[] */
    public function findByProvider(ServiceProvider $provider, ?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.serviceProvider = :sp')->setParameter('sp', $provider)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults($limit)->setFirstResult($offset);

        if ($status) {
            $qb->andWhere('o.status = :s')->setParameter('s', $status);
        }

        return $qb->getQuery()->getResult();
    }

    public function withdraw(Offer $offer, bool $flush = true): void
    {
        if ($offer->getStatus() === Offer::STATUS_ACCEPTED || $offer->getStatus() === Offer::STATUS_REJECTED) {
            // akceptovaná/odmítnutá už by se neměla stahovat
            return;
        }
        $offer->setStatus(Offer::STATUS_WITHDRAWN);
        $this->getEntityManager()->persist($offer);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function expire(Offer $offer, bool $flush = true): void
    {
        if ($offer->getStatus() === Offer::STATUS_ACTIVE) {
            $offer->setStatus(Offer::STATUS_EXPIRED);
            $this->getEntityManager()->persist($offer);
            if ($flush) { $this->getEntityManager()->flush(); }
        }
    }

    public function findOneByQuoteAndProvider(Quote $quote, ServiceProvider $provider): ?Offer
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.quote = :q')->setParameter('q', $quote)
            ->andWhere('o.serviceProvider = :sp')->setParameter('sp', $provider)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Má provider u této poptávky aktivní nabídku?
     */
    public function hasActiveByQuoteAndProvider(Quote $quote, ServiceProvider $provider): bool
    {
        $count = (int) $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.quote = :q')
            ->andWhere('o.serviceProvider = :p')
            ->andWhere('o.status = :active')
            ->setParameter('q', $quote)
            ->setParameter('p', $provider)
            ->setParameter('active', 'active')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Kolik aktivních/stažených nabídek má poptávka – pro přehledy/badges.
     * Vrací pole: ['total' => int, 'active' => int, 'withdrawn' => int]
     */
    public function countByQuoteGrouped(Quote $quote): array
    {
        $rows = $this->createQueryBuilder('o')
            ->select('o.status AS status, COUNT(o.id) AS cnt')
            ->andWhere('o.quote = :q')
            ->groupBy('o.status')
            ->setParameter('q', $quote)
            ->getQuery()
            ->getArrayResult();

        $out = ['total' => 0, 'active' => 0, 'withdrawn' => 0];
        foreach ($rows as $r) {
            $cnt = (int) $r['cnt'];
            $out['total'] += $cnt;
            if (array_key_exists($r['status'], $out)) {
                $out[$r['status']] = $cnt;
            }
        }
        return $out;
    }

    /** Povolit smazání jen vlastních withdrawn nabídek – pomocná utilita */
    public function removeIfWithdrawn(Offer $offer): bool
    {
        if ($offer->getStatus() !== 'withdrawn') {
            return false;
        }
        $em = $this->getEntityManager();
        $em->remove($offer);
        $em->flush();

        return true;
    }
}
