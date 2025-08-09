<?php

namespace App\Entity\Web;

use App\Repository\Web\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntityRepository::class)]
class Entity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'entity')]
    private Collection $entityImages;

    /**
     * @var Collection<int, DateLog>
     */
    #[ORM\OneToMany(targetEntity: DateLog::class, mappedBy: 'entity')]
    private Collection $entityDateLogs;

    public function __construct()
    {
        $this->entityImages = new ArrayCollection();
        $this->entityDateLogs = new ArrayCollection();
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

    /**
     * @return Collection<int, Image>
     */
    public function getEntityImages(): Collection
    {
        return $this->entityImages;
    }

    public function addEntityImage(Image $entityImage): static
    {
        if (!$this->entityImages->contains($entityImage)) {
            $this->entityImages->add($entityImage);
            $entityImage->setEntity($this);
        }

        return $this;
    }

    public function removeEntityImage(Image $entityImage): static
    {
        if ($this->entityImages->removeElement($entityImage)) {
            // set the owning side to null (unless already changed)
            if ($entityImage->getEntity() === $this) {
                $entityImage->setEntity(null);
            }
        }

        return $this;
    }
}
