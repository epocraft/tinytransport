<?php

namespace App\Entity\Admin;

use App\Repository\Admin\ProjectContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectContactRepository::class)]
class ProjectContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $piName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $piPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $piMobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $piFax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $piEmail = null;

    #[ORM\ManyToOne(inversedBy: 'projectProjectContacts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPiName(): ?string
    {
        return $this->piName;
    }

    public function setPiName(string $piName): static
    {
        $this->piName = $piName;

        return $this;
    }

    public function getPiPhone(): ?string
    {
        return $this->piPhone;
    }

    public function setPiPhone(?string $piPhone): static
    {
        $this->piPhone = $piPhone;

        return $this;
    }

    public function getPiMobile(): ?string
    {
        return $this->piMobile;
    }

    public function setPiMobile(?string $piMobile): static
    {
        $this->piMobile = $piMobile;

        return $this;
    }

    public function getPiFax(): ?string
    {
        return $this->piFax;
    }

    public function setPiFax(?string $piFax): static
    {
        $this->piFax = $piFax;

        return $this;
    }

    public function getPiEmail(): ?string
    {
        return $this->piEmail;
    }

    public function setPiEmail(?string $piEmail): static
    {
        $this->piEmail = $piEmail;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }
}