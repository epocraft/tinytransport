<?php

namespace App\Entity\Web;

use App\Repository\Web\WeightUnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WeightUnitRepository::class)]
class WeightUnit
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
    #[ORM\OneToMany(targetEntity: QuoteItem::class, mappedBy: 'weightUnit')]
    private Collection $weightUnitQuoteItems;

    public function __construct()
    {
        $this->weightUnitQuoteItems = new ArrayCollection();
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
    public function getWeightUnitQuoteItems(): Collection
    {
        return $this->weightUnitQuoteItems;
    }

    public function addWeightUnitQuoteItem(QuoteItem $weightUnitQuoteItem): static
    {
        if (!$this->weightUnitQuoteItems->contains($weightUnitQuoteItem)) {
            $this->weightUnitQuoteItems->add($weightUnitQuoteItem);
            $weightUnitQuoteItem->setWeightUnit($this);
        }

        return $this;
    }

    public function removeWeightUnitQuoteItem(QuoteItem $weightUnitQuoteItem): static
    {
        if ($this->weightUnitQuoteItems->removeElement($weightUnitQuoteItem)) {
            // set the owning side to null (unless already changed)
            if ($weightUnitQuoteItem->getWeightUnit() === $this) {
                $weightUnitQuoteItem->setWeightUnit(null);
            }
        }

        return $this;
    }
}
