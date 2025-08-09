<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceProvider;
use App\Entity\Web\ServiceProviderContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceProviderContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProviderContact::class); }

    public function add(ServiceProviderContact $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceProviderContact $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** @return ServiceProviderContact[] */
    public function findPublishedForProvider(ServiceProvider $sp): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.serviceProvider = :sp')->setParameter('sp', $sp)
            ->andWhere('c.publication = 1')
            ->orderBy('c.id', 'ASC')->getQuery()->getResult();
    }

    public function findDisplayContact(ServiceProvider $sp): ?ServiceProviderContact
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.serviceProvider = :sp')->setParameter('sp', $sp)
            // pokud máš sloupec isPrimary, upřednostni ho; jinak prostě nejstarší/základní
            ->addOrderBy('c.isPrimary', 'DESC')
            ->addOrderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * "Preferovaný" veřejný kontakt ke konkrétnímu poskytovateli.
     *
     * ⚠️ Záměrně NEpoužívá žádné pole typu ciEmail/ciPhone v DQL,
     * protože jejich názvy se mohou lišit od mapovaných property v entity.
     * Vrací prostě první kontakt dle ID (ASC). Filtrovat na e‑mail/telefon
     * může až template podle dostupných getterů.
     */
    public function findPreferredForProvider(ServiceProvider $sp): ?ServiceProviderContact
    {
        return $this->createQueryBuilder('spc')
            ->andWhere('spc.serviceProvider = :sp')
            ->setParameter('sp', $sp)
            ->orderBy('spc.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
