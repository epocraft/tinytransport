<?php

namespace App\Repository\Web;

use App\Entity\Web\OfferUnlock;
use App\Entity\Web\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Payment::class); }

    public function add(Payment $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(Payment $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    public function findOneByVs(string $vs): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.vs = :vs')->setParameter('vs', $vs)
            ->getQuery()->getOneOrNullResult();
    }

    public function markSettled(Payment $p, ?string $txId = null, ?\DateTimeInterface $paidAt = null, bool $flush = true): void
    {
        $p->setStatus(Payment::STATUS_SETTLED);
        $p->setTxId($txId);
        $p->setPaidAt($paidAt ?? new \DateTimeImmutable());
        $this->add($p, $flush);
    }

    /** Vrátí součet přijatých plateb pro daný unlock (string pro přesnost DECIMAL). */
    public function getSettledTotalForUnlock(OfferUnlock $unlock): string
    {
        $val = $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.amountTotal), 0) as total')
            ->andWhere('p.unlock = :u')->setParameter('u', $unlock)
            ->andWhere('p.status = :s')->setParameter('s', Payment::STATUS_SETTLED)
            ->getQuery()->getSingleScalarResult();

        return (string)$val;
    }

    /** Zruší všechny pending platby k danému unlocku. */
    public function cancelPendingForUnlock(\App\Entity\Web\OfferUnlock $unlock, bool $flush = true): int
    {
        $payments = $this->findBy(['unlock' => $unlock, 'status' => 'pending']);

        $em = $this->getEntityManager();
        foreach ($payments as $p) {
            $p->setStatus('cancelled'); // nebo Payment::STATUS_CANCELLED, pokud máš konstantu
            $em->persist($p);
        }
        if ($flush) { $em->flush(); }

        return count($payments);
    }

    /** Vrátí poslední platební záznam k danému unlocku (libovolný status), nebo null. */
    public function findLatestForUnlock(\App\Entity\Web\OfferUnlock $unlock): ?\App\Entity\Web\Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.unlock = :u')->setParameter('u', $unlock)
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }
}
