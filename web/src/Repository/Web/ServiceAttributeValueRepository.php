<?php

namespace App\Repository\Web;

use App\Entity\Web\ServiceAttributeDefinition;
use App\Entity\Web\ServiceAttributeValue;
use App\Entity\Web\ServiceProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ServiceAttributeValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry) { parent::__construct($registry, ServiceAttributeValue::class); }

    public function add(ServiceAttributeValue $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->persist($e); if ($flush) $em->flush();
    }

    public function remove(ServiceAttributeValue $e, bool $flush = false): void
    {
        $em = $this->getEntityManager(); $em->remove($e); if ($flush) $em->flush();
    }

    /** Upsert hodnoty podle typu definice; ostatní value_* nulujeme, ať je to čisté. */
    public function upsertValue(ServiceProvider $sp, ServiceAttributeDefinition $def, mixed $value, bool $flush = true): ServiceAttributeValue
    {
        $em = $this->getEntityManager();
        $val = $this->findOneBy(['serviceProvider' => $sp, 'attributeDefinition' => $def]) ?? (new ServiceAttributeValue())
            ->setServiceProvider($sp)->setAttributeDefinition($def);

        // reset
        $val->setValueText(null)->setValueInt(null)->setValueDecimal(null)->setValueBool(null);

        switch ($def->getDatatype()) {
            case ServiceAttributeDefinition::TYPE_TEXT:
                $val->setValueText($value !== null ? (string)$value : null);
                break;
            case ServiceAttributeDefinition::TYPE_INT:
                $val->setValueInt($value !== null ? (int)$value : null);
                break;
            case ServiceAttributeDefinition::TYPE_DECIMAL:
                $val->setValueDecimal($value !== null ? (string)$value : null);
                break;
            case ServiceAttributeDefinition::TYPE_BOOL:
                $val->setValueBool($value === null ? null : (bool)$value);
                break;
        }

        $em->persist($val);
        if ($flush) { $em->flush(); }
        return $val;
    }

    /**
     * Vrátí mapu code => scalar string/int/bool
     * (převod podle datatype definice).
     *
     * @return array<string, mixed>
     */
    public function getAllForProviderMap(ServiceProvider $sp): array
    {
        $qb = $this->createQueryBuilder('v')
            ->leftJoin('v.attributeDefinition', 'd')->addSelect('d')
            ->andWhere('v.serviceProvider = :sp')->setParameter('sp', $sp);

        $rows = $qb->getQuery()->getResult();

        $out = [];
        foreach ($rows as $v) {
            /** @var ServiceAttributeValue $v */
            $d = $v->getAttributeDefinition();
            $code = $d->getCode();
            $out[$code] = match ($d->getDatatype()) {
                ServiceAttributeDefinition::TYPE_TEXT    => $v->getValueText(),
                ServiceAttributeDefinition::TYPE_INT     => $v->getValueInt(),
                ServiceAttributeDefinition::TYPE_DECIMAL => $v->getValueDecimal(),
                ServiceAttributeDefinition::TYPE_BOOL    => $v->getValueBool(),
                default => null,
            };
        }
        return $out;
    }
}
