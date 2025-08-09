<?php

namespace App\Repository\Web;

use App\Entity\Web\Offer;
use App\Entity\Web\OfferUnlock;
use App\Entity\Web\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OfferUnlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, OfferUnlock::class); }

    public function add(OfferUnlock $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(OfferUnlock $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    public function findOneActiveFor(Offer $offer, User $customer): ?OfferUnlock
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.offer = :o')->setParameter('o', $offer)
            ->andWhere('u.customer = :c')->setParameter('c', $customer)
            ->andWhere('u.status = :s')->setParameter('s', OfferUnlock::STATUS_ACTIVE)
            ->getQuery()->getOneOrNullResult();
    }

    /** @return OfferUnlock[] */
    public function findPendingForCustomer(User $customer): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.customer = :c')->setParameter('c', $customer)
            ->andWhere('u.status = :s')->setParameter('s', OfferUnlock::STATUS_PENDING)
            ->orderBy('u.createdAt', 'DESC')->getQuery()->getResult();
    }

    public function activate(OfferUnlock $unlock, ?\DateTimeInterface $unlockedAt = null, bool $flush = true): void
    {
        $unlock->setStatus(OfferUnlock::STATUS_ACTIVE);
        $unlock->setUnlockedAt($unlockedAt ?? new \DateTimeImmutable());
        $this->add($unlock, $flush);
    }

    public function cancel(OfferUnlock $unlock, bool $flush = true): void
    {
        $unlock->setStatus(OfferUnlock::STATUS_CANCELLED);
        $this->add($unlock, $flush);
    }

    public function expireOlderThan(\DateTimeInterface $dt, bool $flush = true): int
    {
        $qb = $this->createQueryBuilder('u')
            ->update()->set('u.status', ':st')->setParameter('st', OfferUnlock::STATUS_EXPIRED)
            ->andWhere('u.status = :curr')->setParameter('curr', OfferUnlock::STATUS_ACTIVE)
            ->andWhere('u.expiresAt IS NOT NULL AND u.expiresAt < :dt')->setParameter('dt', $dt);

        $res = $qb->getQuery()->execute();
        if ($flush) { $this->getEntityManager()->flush(); }
        return (int)$res;
    }

    public function findOnePendingFor(
        Offer $offer,
        User $customer
    ): ?OfferUnlock {
        return $this->findOneBy([
            'offer'    => $offer,
            'customer' => $customer,
            'status'   => OfferUnlock::STATUS_PENDING, // 'pending'
        ]);
    }

    /** Označ pending unlock jako 'expired' (zrušené před dokončením platby). */
    public function expire(
        OfferUnlock $unlock,
        bool $flush = true
    ): void {
        $unlock->setStatus(OfferUnlock::STATUS_EXPIRED); // 'expired'
        $unlock->setExpiresAt(new \DateTimeImmutable());
        $this->_em->persist($unlock);
        if ($flush) { $this->_em->flush(); }
    }

    /** Najde existující unlock pro dvojici (offer, customer) bez ohledu na status. */
    public function findAnyFor(\App\Entity\Web\Offer $offer, \App\Entity\Web\User $customer): ?\App\Entity\Web\OfferUnlock
    {
        return $this->findOneBy([
            'offer'    => $offer,
            'customer' => $customer,
        ]);
    }

    /** Reaktivuje existující unlock (např. z EXPIRED) na PENDING a nastaví nové časy. */
    public function reactivateToPending(
        \App\Entity\Web\OfferUnlock $unlock,
        \DateTimeImmutable $now,
        \DateTimeImmutable $expiresAt,
        bool $flush = true
    ): \App\Entity\Web\OfferUnlock {
        $unlock->setStatus(\App\Entity\Web\OfferUnlock::STATUS_PENDING);
        $unlock->setCreatedAt($now);
        $unlock->setExpiresAt($expiresAt);

        $em = $this->getEntityManager();
        $em->persist($unlock);
        if ($flush) { $em->flush(); }

        return $unlock;
    }

}
