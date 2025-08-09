<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'quote_item')]
class QuoteItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // FK: quote_item.quote_id → quote.id
    #[ORM\ManyToOne(targetEntity: Quote::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'quote_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Quote $quote;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    // DECIMAL → string
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $height = null;

    #[ORM\Column(name: 'height_unit_id', type: 'integer', nullable: true)]
    private ?int $heightUnitId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $width = null;

    #[ORM\Column(name: 'width_unit_id', type: 'integer', nullable: true)]
    private ?int $widthUnitId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $depth = null;

    #[ORM\Column(name: 'depth_unit_id', type: 'integer', nullable: true)]
    private ?int $depthUnitId = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $weight = null;

    #[ORM\Column(name: 'weight_unit_id', type: 'integer', nullable: true)]
    private ?int $weightUnitId = null;

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getQuote(): Quote { return $this->quote; }
    public function setQuote(Quote $quote): self { $this->quote = $quote; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getHeight(): ?string { return $this->height; }
    public function setHeight(?string $height): self { $this->height = $height; return $this; }

    public function getHeightUnitId(): ?int { return $this->heightUnitId; }
    public function setHeightUnitId(?int $heightUnitId): self { $this->heightUnitId = $heightUnitId; return $this; }

    public function getWidth(): ?string { return $this->width; }
    public function setWidth(?string $width): self { $this->width = $width; return $this; }

    public function getWidthUnitId(): ?int { return $this->widthUnitId; }
    public function setWidthUnitId(?int $widthUnitId): self { $this->widthUnitId = $widthUnitId; return $this; }

    public function getDepth(): ?string { return $this->depth; }
    public function setDepth(?string $depth): self { $this->depth = $depth; return $this; }

    public function getDepthUnitId(): ?int { return $this->depthUnitId; }
    public function setDepthUnitId(?int $depthUnitId): self { $this->depthUnitId = $depthUnitId; return $this; }

    public function getWeight(): ?string { return $this->weight; }
    public function setWeight(?string $weight): self { $this->weight = $weight; return $this; }

    public function getWeightUnitId(): ?int { return $this->weightUnitId; }
    public function setWeightUnitId(?int $weightUnitId): self { $this->weightUnitId = $weightUnitId; return $this; }

    public function __toString(): string
    {
        return 'QuoteItem#'.$this->id;
    }
}
