<?php

namespace App\Entity\Web;

use App\Repository\Web\LengthUnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LengthUnitRepository::class)]
class LengthUnit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $publication = null;

    /**
     * @var Collection<int, QuoteItem>
     */
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'depthUnit')]
    private Collection $depthUnitQuoteItems;

    /**
     * @var Collection<int, QuoteItem>
     */
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'widthUnit')]
    private Collection $widthUnitQuoteItems;

    /**
     * @var Collection<int, QuoteItem>
     */
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'heightUnit')]
    private Collection $heightUnitQuoteItems;

    public function __construct()
    {
        $this->depthUnitQuoteItems = new ArrayCollection();
        $this->widthUnitQuoteItems = new ArrayCollection();
        $this->heightUnitQuoteItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPublication(): ?int
    {
        return $this->publication;
    }

    public function setPublication(int $publication): static
    {
        $this->publication = $publication;

        return $this;
    }

    /**
     * @return Collection<int, QuoteItem>
     */
    public function getDepthUnitQuoteItems(): Collection
    {
        return $this->depthUnitQuoteItems;
    }

    public function addDepthUnitQuoteItem(QuoteItem $depthUnitQuoteItem): static
    {
        if (!$this->depthUnitQuoteItems->contains($depthUnitQuoteItem)) {
            $this->depthUnitQuoteItems->add($depthUnitQuoteItem);
            $depthUnitQuoteItem->setDepthUnit($this);
        }

        return $this;
    }

    public function removeDepthUnitQuoteItem(QuoteItem $depthUnitQuoteItem): static
    {
        if ($this->depthUnitQuoteItems->removeElement($depthUnitQuoteItem)) {
            // set the owning side to null (unless already changed)
            if ($depthUnitQuoteItem->getDepthUnit() === $this) {
                $depthUnitQuoteItem->setDepthUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuoteItem>
     */
    public function getWidthUnitQuoteItems(): Collection
    {
        return $this->widthUnitQuoteItems;
    }

    public function addWidthUnitQuoteItem(QuoteItem $widthUnitQuoteItem): static
    {
        if (!$this->widthUnitQuoteItems->contains($widthUnitQuoteItem)) {
            $this->widthUnitQuoteItems->add($widthUnitQuoteItem);
            $widthUnitQuoteItem->setWidthUnit($this);
        }

        return $this;
    }

    public function removeWidthUnitQuoteItem(QuoteItem $widthUnitQuoteItem): static
    {
        if ($this->widthUnitQuoteItems->removeElement($widthUnitQuoteItem)) {
            // set the owning side to null (unless already changed)
            if ($widthUnitQuoteItem->getWidthUnit() === $this) {
                $widthUnitQuoteItem->setWidthUnit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, QuoteItem>
     */
    public function getHeightUnitQuoteItems(): Collection
    {
        return $this->heightUnitQuoteItems;
    }

    public function addHeightUnitQuoteItem(QuoteItem $heightUnitQuoteItem): static
    {
        if (!$this->heightUnitQuoteItems->contains($heightUnitQuoteItem)) {
            $this->heightUnitQuoteItems->add($heightUnitQuoteItem);
            $heightUnitQuoteItem->setHeightUnit($this);
        }

        return $this;
    }

    public function removeHeightUnitQuoteItem(QuoteItem $heightUnitQuoteItem): static
    {
        if ($this->heightUnitQuoteItems->removeElement($heightUnitQuoteItem)) {
            // set the owning side to null (unless already changed)
            if ($heightUnitQuoteItem->getHeightUnit() === $this) {
                $heightUnitQuoteItem->setHeightUnit(null);
            }
        }

        return $this;
    }
}
