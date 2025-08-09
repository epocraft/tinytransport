<?php

namespace App\Entity\Admin;

use App\Repository\Admin\ProjectCookieConsentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectCookieConsentRepository::class)]
class ProjectCookieConsent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'projectProjectCookieConsent', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $consentType = null;

    #[ORM\Column(length: 255)]
    private ?string $noticeBannerType = null;

    #[ORM\Column(length: 255)]
    private ?string $palette = null;

    #[ORM\Column]
    private ?bool $noticeBannerRejectButtonHide = null;

    #[ORM\Column]
    private ?bool $preferencesCenterCloseButtonHide = null;

    #[ORM\Column]
    private ?bool $pageRefreshConfirmationButtons = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $publication = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getConsentType(): ?string
    {
        return $this->consentType;
    }

    public function setConsentType(string $consentType): static
    {
        $this->consentType = $consentType;

        return $this;
    }

    public function getNoticeBannerType(): ?string
    {
        return $this->noticeBannerType;
    }

    public function setNoticeBannerType(string $noticeBannerType): static
    {
        $this->noticeBannerType = $noticeBannerType;

        return $this;
    }

    public function getPalette(): ?string
    {
        return $this->palette;
    }

    public function setPalette(string $palette): static
    {
        $this->palette = $palette;

        return $this;
    }

    public function isNoticeBannerRejectButtonHide(): ?bool
    {
        return $this->noticeBannerRejectButtonHide;
    }

    public function setNoticeBannerRejectButtonHide(bool $noticeBannerRejectButtonHide): static
    {
        $this->noticeBannerRejectButtonHide = $noticeBannerRejectButtonHide;

        return $this;
    }

    public function isPreferencesCenterCloseButtonHide(): ?bool
    {
        return $this->preferencesCenterCloseButtonHide;
    }

    public function setPreferencesCenterCloseButtonHide(bool $preferencesCenterCloseButtonHide): static
    {
        $this->preferencesCenterCloseButtonHide = $preferencesCenterCloseButtonHide;

        return $this;
    }

    public function isPageRefreshConfirmationButtons(): ?bool
    {
        return $this->pageRefreshConfirmationButtons;
    }

    public function setPageRefreshConfirmationButtons(bool $pageRefreshConfirmationButtons): static
    {
        $this->pageRefreshConfirmationButtons = $pageRefreshConfirmationButtons;

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
}
