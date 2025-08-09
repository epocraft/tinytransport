<?php

namespace App\Repository\Web;

use App\Entity\Web\Category;
use App\Entity\Web\Offer;
use App\Entity\Web\Quote;
use App\Entity\Web\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, Quote::class); }

    public function add(Quote $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->persist($entity);
        if ($flush) { $em->flush(); }
    }

    public function remove(Quote $entity, bool $flush = false): void
    {
        $em = $this->getEntityManager();
        $em->remove($entity);
        if ($flush) { $em->flush(); }
    }

    /** @return Quote[] */
    public function findForCustomer(User $customer, ?string $status = null, int $limit = 50, int $offset = 0): array
    {
        $qb = $this->createQueryBuilder('q')
            ->andWhere('q.user = :u')->setParameter('u', $customer)
            ->orderBy('q.createdAt', 'DESC')
            ->setMaxResults($limit)->setFirstResult($offset);

        if ($status) {
            $qb->andWhere('q.status = :s')->setParameter('s', $status);
        }

        return $qb->getQuery()->getResult();
    }

    /** @return Quote[] */
    public function findOpenByCategory(Category $category, int $limit = 50): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.category = :c')->setParameter('c', $category)
            ->andWhere('q.status = :s')->setParameter('s', Quote::STATUS_OPEN)
            ->orderBy('q.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /**
     * Vybere nabídku pro poptávku:
     * - zkontroluje, že nabídka patří k poptávce
     * - nastaví offer->status = accepted + acceptedAt
     * - ostatní nabídky k téže poptávce nastaví na rejected (pokud už nejsou accepted/rejected)
     * - quote->status = selected, chosenOffer, chosenAt
     */
    public function chooseOffer(Quote $quote, Offer $offer, bool $flush = true): void
    {
        if ($offer->getQuote()->getId() !== $quote->getId()) {
            throw new InvalidArgumentException('Offer nepatří k dané poptávce.');
        }

        if (!in_array($quote->getStatus(), [Quote::STATUS_OPEN, Quote::STATUS_SELECTED], true)) {
            throw new InvalidArgumentException('Poptávku v tomto stavu nelze vybírat.');
        }

        $em = $this->getEntityManager();

        $em->wrapInTransaction(function() use ($em, $quote, $offer, $flush) {
            // Reject všech ostatních nabídek k téže poptávce
            foreach ($quote->getOffers() as $o) {
                if ($o->getId() !== $offer->getId() && !in_array($o->getStatus(), [Offer::STATUS_REJECTED, Offer::STATUS_ACCEPTED], true)) {
                    $o->setStatus(Offer::STATUS_REJECTED);
                    $em->persist($o);
                }
            }

            // Accept vybrané nabídky
            $offer->setStatus(Offer::STATUS_ACCEPTED);
            $offer->setAcceptedAt(new \DateTimeImmutable());
            $em->persist($offer);

            // Update poptávky
            $quote->setChosenOffer($offer);
            $quote->setChosenAt(new \DateTimeImmutable());
            $quote->setStatus(Quote::STATUS_SELECTED);
            $em->persist($quote);

            if ($flush) { $em->flush(); }
        });
    }

    /**
     * Uzavře poptávku (např. po dokončení). Nemění přijatou nabídku.
     */
    public function closeQuote(Quote $quote, bool $flush = true): void
    {
        if (!in_array($quote->getStatus(), [Quote::STATUS_SELECTED, Quote::STATUS_OPEN], true)) {
            throw new InvalidArgumentException('Poptávku v tomto stavu nelze uzavřít.');
        }
        $quote->setStatus(Quote::STATUS_CLOSED);
        $quote->setClosedAt(new \DateTimeImmutable());
        $this->getEntityManager()->persist($quote);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    /**
     * Zruší poptávku (např. zákazníkem). Ostatní nabídky ponechá beze změny.
     */
    public function cancelQuote(Quote $quote, bool $flush = true): void
    {
        if ($quote->getStatus() === Quote::STATUS_CLOSED) {
            throw new InvalidArgumentException('Uzavřenou poptávku nelze zrušit.');
        }
        $quote->setStatus(Quote::STATUS_CANCELLED);
        $this->getEntityManager()->persist($quote);
        if ($flush) { $this->getEntityManager()->flush(); }
    }
}
