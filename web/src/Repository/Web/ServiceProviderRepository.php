<?php

namespace App\Repository\Web;

use App\Entity\Web\Category;
use App\Entity\Web\ServiceProvider;
use App\Entity\Web\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\ArrayParameterType;

/**
 * @extends ServiceEntityRepository<ServiceProvider>
 */
class ServiceProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceProvider::class); }

    public function add(ServiceProvider $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    public function remove(ServiceProvider $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) { $this->getEntityManager()->flush(); }
    }

    /**
     * @return ServiceProvider[]
     */
    public function findPublished(?array $categoryIds = null, int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('sp')
            ->andWhere('sp.publication = 1')
            ->orderBy('sp.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($categoryIds) {
            $qb->join('sp.categories', 'c')
               ->andWhere('c.id IN (:cids)')
               ->setParameter('cids', $categoryIds);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ServiceProvider[]
     */
    public function findByOwner(User $owner, int $limit = 50): array
    {
        return $this->createQueryBuilder('sp')
            ->andWhere('sp.owner = :u')->setParameter('u', $owner)
            ->orderBy('sp.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    /**
     * Načte „profil“ providera s připravenými kolekcemi (addresses, contacts, vehicles, openingHours, openingExceptions).
     */
    public function loadProfile(int $id): ?ServiceProvider
    {
        return $this->createQueryBuilder('sp')
            ->leftJoin('sp.addresses', 'addr')->addSelect('addr')
            ->leftJoin('sp.contacts', 'ct')->addSelect('ct')
            ->leftJoin('sp.vehicles', 'vh')->addSelect('vh')
            ->leftJoin('sp.openingHours', 'oh')->addSelect('oh')
            ->leftJoin('sp.openingExceptions', 'oe')->addSelect('oe')
            ->leftJoin('sp.categories', 'cat')->addSelect('cat')
            ->andWhere('sp.id = :id')->setParameter('id', $id)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * Vyhledávání providerů podle vzdálenosti + atributových filtrů.
     * Používá nativní SQL (navazuje na view v_service_provider_attributes).
     *
     * $attributeFilters tvar:
     *   ['tail_lift' => true, 'capacity_kg' => ['min' => 1200], 'weekend_work' => 1]
     *
     * Vrací pole asociativních řádků:
     *   id, name, city, distance_km
     */
    public function searchByDistanceAndAttributes(
    float $lat,
    float $lng,
    ?int $maxDistanceKm,
    array $categoryIds = [],
    array $attributeFilters = [],
    int $limit = 50,
    int $offset = 0,
    bool $publishedOnly = true
): array {
    $conn = $this->getEntityManager()->getConnection();

    $sql = <<<SQL
SELECT
  sp.id,
  sp.name,
  spa.city,
  ROUND(6371 * ACOS(
    COS(RADIANS(:lat)) * COS(RADIANS(spa.lat)) *
    COS(RADIANS(spa.lng) - RADIANS(:lng)) +
    SIN(RADIANS(:lat)) * SIN(RADIANS(spa.lat))
  ), 1) AS distance_km
FROM service_provider sp
JOIN service_provider_address spa
  ON spa.service_provider_id = sp.id AND spa.is_primary = 1
SQL;

    $params = ['lat' => $lat, 'lng' => $lng, 'pubOnly' => $publishedOnly ? 1 : 0];
    $types  = [];

    if (!empty($categoryIds)) {
        $sql .= "\nJOIN service_provider_category spc ON spc.service_provider_id = sp.id\n";
    }

    $attrJoin = "";
    if (!empty($attributeFilters)) {
        $attrJoin = "
JOIN (
  SELECT
    a.service_provider_id,
    " . $this->buildAttributeSelectCases($attributeFilters) . "
  FROM v_service_provider_attributes a
  GROUP BY a.service_provider_id
) af ON af.service_provider_id = sp.id
";
    }

    // !!! změna tady: WHERE (:pubOnly = 0 OR sp.publication = 1)
    $sql .= $attrJoin . " WHERE (:pubOnly = 0 OR sp.publication = 1)";

    if (!empty($categoryIds)) {
        $sql .= " AND spc.category_id IN (:cids)";
        $params['cids'] = $categoryIds;
        $types['cids'] = \Doctrine\DBAL\ArrayParameterType::INTEGER;
    }

    if ($maxDistanceKm !== null) {
        $sql .= " HAVING distance_km <= :maxd";
        $params['maxd'] = $maxDistanceKm;
    } else {
        $sql .= " HAVING 1=1";
    }

    if (!empty($attributeFilters)) {
        foreach ($attributeFilters as $code => $cond) {
            $col = 'attr_' . $code;
            if (is_array($cond)) {
                if (isset($cond['min'])) { $sql .= " AND COALESCE($col, 0) >= :{$col}_min"; $params["{$col}_min"] = $cond['min']; }
                if (isset($cond['max'])) { $sql .= " AND COALESCE($col, 0) <= :{$col}_max"; $params["{$col}_max"] = $cond['max']; }
                if (isset($cond['eq']))  { $sql .= " AND $col = :{$col}_eq"; $params["{$col}_eq"] = $cond['eq']; }
            } else {
                $sql .= " AND $col = :{$col}_eq";
                $params["{$col}_eq"] = $cond;
            }
        }
    }

    $sql .= " ORDER BY distance_km ASC, sp.name ASC LIMIT :limit OFFSET :offset";
    $params['limit'] = $limit;
    $params['offset'] = $offset;

    $stmt = $conn->prepare($sql);
    foreach ($params as $k => $v) {
        $type = $types[$k] ?? null;
        $stmt->bindValue($k, $v, $type);
    }
    return $stmt->executeQuery()->fetchAllAssociative();
}

    /**
     * Pomocný builder CASE výrazů pro subselect atributů.
     * Výstup vytvoří sloupce: attr_CODE pro každý požadovaný kód.
     */
    private function buildAttributeSelectCases(array $attributeFilters): string
    {
        $cases = [];
        foreach ($attributeFilters as $code => $_) {
            $cases[] = "MAX(CASE WHEN a.attribute_code = '".$code."' THEN
                COALESCE(a.value_int, a.value_decimal, a.value_bool, NULLIF(a.value_text,'')) END) AS attr_".$code;
        }
        return implode(",\n    ", $cases);
    }
}
