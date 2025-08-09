<?php

namespace App\Entity\Admin;

use App\Repository\Admin\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EntityRepository::class)]

#[UniqueEntity(
    fields: ['name'],
    message: 'error.name.data_already_exists',
)]

class Entity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, DateLog>
     */
    #[ORM\OneToMany(targetEntity: DateLog::class, mappedBy: 'entity')]
    private Collection $entityDateLogs;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'entity')]
    private Collection $entityDocuments;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'entity')]
    private Collection $entityImages;

    public function __construct()
    {
        $this->entityDateLogs = new ArrayCollection();
        $this->entityImages = new ArrayCollection();
        $this->entityDocuments = new ArrayCollection();
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
     * @return Collection<int, DateLog>
     */
    public function getEntityDateLogs(): Collection
    {
        return $this->entityDateLogs;
    }

    public function addEntityDateLog(DateLog $entityDateLog): static
    {
        if (!$this->entityDateLogs->contains($entityDateLog)) {
            $this->entityDateLogs->add($entityDateLog);
            $entityDateLog->setEntity($this);
        }

        return $this;
    }

    public function removeEntityDateLog(DateLog $entityDateLog): static
    {
        if ($this->entityDateLogs->removeElement($entityDateLog)) {
            // set the owning side to null (unless already changed)
            if ($entityDateLog->getEntity() === $this) {
                $entityDateLog->setEntity(null);
            }
        }

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

    /**
     * @return Collection<int, Document>
     */
    public function getEntityDocuments(): Collection
    {
        return $this->entityDocuments;
    }

    public function addEntityDocument(Document $entityDocument): static
    {
        if (!$this->entityDocuments->contains($entityDocument)) {
            $this->entityDocuments->add($entityDocument);
            $entityDocument->setEntity($this);
        }

        return $this;
    }

    public function removeEntityDocument(Document $entityDocument): static
    {
        if ($this->entityDocuments->removeElement($entityDocument)) {
            // set the owning side to null (unless already changed)
            if ($entityDocument->getEntity() === $this) {
                $entityDocument->setEntity(null);
            }
        }

        return $this;
    }
}
