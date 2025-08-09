<?php

namespace App\Entity\Web;

use App\Repository\Web\UserSettingsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userUserSettings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Language::class, inversedBy: 'languageUserSettings', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?Language $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateFormat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $dateTimeFormat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $htmlSignature = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $separatorOfThousands = null;

    #[ORM\Column(length: 1, nullable: true)]
    private ?string $decimalPoint = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getDateFormat(): ?string
    {
        return $this->dateFormat;
    }

    public function setDateFormat(?string $dateFormat): static
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    public function getDateTimeFormat(): ?string
    {
        return $this->dateTimeFormat;
    }

    public function setDateTimeFormat(?string $dateTimeFormat): static
    {
        $this->dateTimeFormat = $dateTimeFormat;

        return $this;
    }

    public function getHtmlSignature(): ?string
    {
        return $this->htmlSignature;
    }

    public function setHtmlSignature(?string $htmlSignature): static
    {
        $this->htmlSignature = $htmlSignature;

        return $this;
    }

    public function getSeparatorOfThousands(): ?string
    {
        return $this->separatorOfThousands;
    }

    public function setSeparatorOfThousands(?string $separatorOfThousands): static
    {
        $this->separatorOfThousands = $separatorOfThousands;

        return $this;
    }

    public function getDecimalPoint(): ?string
    {
        return $this->decimalPoint;
    }

    public function setDecimalPoint(?string $decimalPoint): static
    {
        $this->decimalPoint = $decimalPoint;

        return $this;
    }
}
