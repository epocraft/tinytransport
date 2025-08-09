<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'category')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'boolean')]
    private bool $publication = true;

    /** @var Collection<int, ServiceProvider> */
    #[ORM\ManyToMany(targetEntity: ServiceProvider::class, mappedBy: 'categories')]
    private Collection $serviceProviders;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->serviceProviders = new ArrayCollection();
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $dt): self { $this->createdAt = $dt; return $this; }

    public function isPublication(): bool { return $this->publication; }
    public function setPublication(bool $publication): self { $this->publication = $publication; return $this; }

    /** @return Collection<int, ServiceProvider> */
    public function getServiceProviders(): Collection { return $this->serviceProviders; }

    public function addServiceProvider(ServiceProvider $sp): self
    {
        if (!$this->serviceProviders->contains($sp)) {
            $this->serviceProviders->add($sp);
            $sp->addCategory($this);
        }
        return $this;
    }

    public function removeServiceProvider(ServiceProvider $sp): self
    {
        if ($this->serviceProviders->removeElement($sp)) {
            $sp->removeCategory($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?: 'Category#'.$this->id;
    }
}
