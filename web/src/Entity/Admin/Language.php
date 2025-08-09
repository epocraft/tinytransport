<?php

namespace App\Entity\Admin;

use App\Repository\Admin\LanguageRepository;
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
    private ?string $name = null;

    #[ORM\Column(length: 2)]
    private ?string $urlAlias = null;

    #[ORM\Column(length: 10)]
    private ?string $locale = null;

    #[ORM\Column(length: 10)]
    private ?string $translate = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $publication = null;

    #[ORM\OneToMany(mappedBy: 'language', targetEntity: ProjectText::class)]
    private Collection $languageProjectTexts;

    #[ORM\OneToOne(mappedBy: 'language', cascade: ['persist', 'remove'])]
    private ?UserSettings $languageUserSettings = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(mappedBy: 'language', targetEntity: Article::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $languageArticles;

    public function __construct()
    {
        $this->languageProjectTexts = new ArrayCollection();
        $this->languageArticles = new ArrayCollection();
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

    /**
     * @return Collection<int, ProjectText>
     */
    public function getLanguageProjectTexts(): Collection
    {
        return $this->languageProjectTexts;
    }

    public function addLanguageProjectText(ProjectText $languageProjectText): static
    {
        if (!$this->languageProjectTexts->contains($languageProjectText)) {
            $this->languageProjectTexts->add($languageProjectText);
            $languageProjectText->setLanguage($this);
        }

        return $this;
    }

    public function removeLanguageProjectText(ProjectText $languageProjectText): static
    {
        if ($this->languageProjectTexts->removeElement($languageProjectText)) {
            // set the owning side to null (unless already changed)
            if ($languageProjectText->getLanguage() === $this) {
                $languageProjectText->setLanguage(null);
            }
        }

        return $this;
    }

    public function getLanguageUserSettings(): ?UserSettings
    {
        return $this->languageUserSettings;
    }

    public function setLanguageUserSettings(UserSettings $languageUserSettings): static
    {
        // set the owning side of the relation if necessary
        if ($languageUserSettings->getLanguage() !== $this) {
            $languageUserSettings->setLanguage($this);
        }

        $this->languageUserSettings = $languageUserSettings;

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
}
