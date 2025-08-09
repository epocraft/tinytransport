<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_attribute_definition')]
#[UniqueEntity(fields: ['category', 'code'], message: 'Kód atributu musí být v rámci kategorie unikátní.')]
class ServiceAttributeDefinition
{
    public const TYPE_TEXT    = 'text';
    public const TYPE_INT     = 'int';
    public const TYPE_DECIMAL = 'decimal';
    public const TYPE_BOOL    = 'bool';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // FK: category_id → category.id
    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private Category $category;

    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $code = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $label = '';

    #[ORM\Column(type: 'string', length: 16)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_TEXT, self::TYPE_INT, self::TYPE_DECIMAL, self::TYPE_BOOL])]
    private string $datatype = self::TYPE_TEXT;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    #[Assert\Length(max: 32)]
    private ?string $unit = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $required = false;

    #[ORM\Column(name: 'options_json', type: 'json', nullable: true)]
    private ?array $optionsJson = null;

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getCategory(): Category { return $this->category; }
    public function setCategory(Category $category): self { $this->category = $category; return $this; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): self { $this->label = $label; return $this; }

    public function getDatatype(): string { return $this->datatype; }
    public function setDatatype(string $datatype): self { $this->datatype = $datatype; return $this; }

    public function getUnit(): ?string { return $this->unit; }
    public function setUnit(?string $unit): self { $this->unit = $unit; return $this; }

    public function isRequired(): bool { return $this->required; }
    public function setRequired(bool $required): self { $this->required = $required; return $this; }

    public function getOptionsJson(): ?array { return $this->optionsJson; }
    public function setOptionsJson(?array $optionsJson): self { $this->optionsJson = $optionsJson; return $this; }

    // --- convenience ---

    public function isNumeric(): bool
    {
        return \in_array($this->datatype, [self::TYPE_INT, self::TYPE_DECIMAL], true);
    }

    public function __toString(): string
    {
        return sprintf('%s.%s', $this->getCategory()->getName(), $this->code);
    }
}
