<?php

namespace App\Entity\Web;

use App\Repository\Web\LanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LanguageRepository::class)]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $shortName = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 2)]
    private ?string $urlAlias = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(length: 10)]
    private ?string $translate = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $publication = null;

    #[ORM\OneToOne(mappedBy: 'language', cascade: ['persist', 'remove'])]
    private ?ProjectText $languageProjectText = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(mappedBy: 'language', targetEntity: Article::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $languageArticles;

    /**
     * @var Collection<int, UserSettings>
     */
    #[ORM\OneToMany(mappedBy: 'language', targetEntity: UserSettings::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $languageUserSettings;

    public function __construct()
    {
        $this->languageArticles = new ArrayCollection();
        $this->languageUserSettings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): static
    {
        $this->shortName = $shortName;

        return $this;
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

    public function getUrlAlias(): ?string
    {
        return $this->urlAlias;
    }

    public function setUrlAlias(string $urlAlias): static
    {
        $this->urlAlias = $urlAlias;

        return $this;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getTranslate(): ?string
    {
        return $this->translate;
    }

    public function setTranslate(string $translate): static
    {
        $this->translate = $translate;

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

    public function getLanguageProjectText(): ?ProjectText
    {
        return $this->languageProjectText;
    }

    public function setLanguageProjectText(ProjectText $languageProjectText): static
    {
        // set the owning side of the relation if necessary
        if ($languageProjectText->getLanguage() !== $this) {
            $languageProjectText->setLanguage($this);
        }

        $this->languageProjectText = $languageProjectText;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getLanguageArticles(): Collection
    {
        return $this->languageArticles;
    }

    public function addLanguageArticle(Article $article): static
    {
        if (!$this->languageArticles->contains($article)) {
            $this->languageArticles[] = $article;
            $article->setLanguage($this);
        }
        return $this;
    }

    public function removeLanguageArticle(Article $article): static
    {
        if ($this->languageArticles->removeElement($article)) {
            if ($article->getLanguage() === $this) {
                $article->setLanguage(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UserSettings>
     */
    public function getLanguageUserSettings(): Collection
    {
        return $this->languageUserSettings;
    }

    public function addLanguageUserSetting(UserSettings $userSetting): static
    {
        if (!$this->languageUserSettings->contains($userSetting)) {
            $this->languageUserSettings[] = $userSetting;
            $userSetting->setLanguage($this);
        }
        return $this;
    }

    public function removeLanguageUserSetting(UserSettings $userSetting): static
    {
        if ($this->languageUserSettings->removeElement($userSetting)) {
            if ($userSetting->getLanguage() === $this) {
                $userSetting->setLanguage(null);
            }
        }
        return $this;
    }
}
