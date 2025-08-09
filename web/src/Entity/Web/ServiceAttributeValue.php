<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'service_attribute_value')]
#[UniqueEntity(fields: ['serviceProvider', 'attributeDefinition'], message: 'Hodnota atributu je v rámci poskytovatele unikátní.')]
class ServiceAttributeValue
{
    // Kompozitní PK: (service_provider_id, attribute_definition_id)
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ServiceProvider::class)]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: ServiceAttributeDefinition::class)]
    #[ORM\JoinColumn(name: 'attribute_definition_id', referencedColumnName: 'id', nullable: false)]
    private ServiceAttributeDefinition $attributeDefinition;

    #[ORM\Column(name: 'value_text', type: 'text', nullable: true)]
    private ?string $valueText = null;

    #[ORM\Column(name: 'value_int', type: 'integer', nullable: true)]
    private ?int $valueInt = null;

    #[ORM\Column(name: 'value_decimal', type: 'decimal', precision: 12, scale: 3, nullable: true)]
    private ?string $valueDecimal = null;

    #[ORM\Column(name: 'value_bool', type: 'boolean', nullable: true)]
    private ?bool $valueBool = null;

    // --- getters/setters ---

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getAttributeDefinition(): ServiceAttributeDefinition { return $this->attributeDefinition; }
    public function setAttributeDefinition(ServiceAttributeDefinition $attributeDefinition): self { $this->attributeDefinition = $attributeDefinition; return $this; }

    public function getValueText(): ?string { return $this->valueText; }
    public function setValueText(?string $valueText): self { $this->valueText = $valueText; return $this; }

    public function getValueInt(): ?int { return $this->valueInt; }
    public function setValueInt(?int $valueInt): self { $this->valueInt = $valueInt; return $this; }

    public function getValueDecimal(): ?string { return $this->valueDecimal; }
    public function setValueDecimal(?string $valueDecimal): self { $this->valueDecimal = $valueDecimal; return $this; }

    public function getValueBool(): ?bool { return $this->valueBool; }
    public function setValueBool(?bool $valueBool): self { $this->valueBool = $valueBool; return $this; }

    // --- convenience ---

    /** Jednotný stringový pohled na hodnotu (užitečné pro log/šablony) */
    public function getValueAsString(): string
    {
        $def = $this->getAttributeDefinition();
        return match ($def->getDatatype()) {
            ServiceAttributeDefinition::TYPE_TEXT    => (string)($this->valueText ?? ''),
            ServiceAttributeDefinition::TYPE_INT     => $this->valueInt !== null ? (string)$this->valueInt : '',
            ServiceAttributeDefinition::TYPE_DECIMAL => $this->valueDecimal ?? '',
            ServiceAttributeDefinition::TYPE_BOOL    => $this->valueBool ? '1' : '0',
            default => '',
        };
    }

    public function __toString(): string
    {
        return $this->getAttributeDefinition()->getCode().': '.$this->getValueAsString();
    }
}
