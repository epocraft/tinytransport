<?php

namespace App\Entity\Web;

use App\Repository\Web\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ciName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciIn = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciTin = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $ciVatPayer = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciBa = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciBc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciIban = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciSwift = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciRegisteredRegister = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciRegisteredOffice = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciRegisteredCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciRegisteredFileNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciDuns = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciDataBox = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciWeb = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciPhoneCode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciPhone = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciMobile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciFax = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ciEmail = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biStreet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biZipcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diStreet = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diCity = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diZipcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $discord = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $facebook = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $instagram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $linkedin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pinterest = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $snapchat = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegram = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tiktok = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tumblr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $x = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $whatsapp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $youtube = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $publication = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $maintenance = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $maintenanceText = null;

    /**
     * @var Collection<int, ProjectApi>
     */
    #[ORM\OneToMany(targetEntity: ProjectApi::class, mappedBy: 'project')]
    private Collection $projectProjectApis;

    /**
     * @var Collection<int, ProjectContact>
     */
    #[ORM\OneToMany(targetEntity: ProjectContact::class, mappedBy: 'project')]
    private Collection $projectProjectContacts;

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?ProjectText $projectProjectText = null;

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?ProjectCookieConsent $projectProjectCookieConsent = null;

    /**
     * @var Collection<int, Article>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Article::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    private Collection $projectArticles;

    public function __construct()
    {
        $this->projectProjectApis = new ArrayCollection();
        $this->projectProjectContacts = new ArrayCollection();
        $this->projectArticles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCiName(): ?string
    {
        return $this->ciName;
    }

    public function setCiName(string $ciName): static
    {
        $this->ciName = $ciName;

        return $this;
    }
    
    public function getCiIn(): ?string
    {
        return $this->ciIn;
    }

    public function setCiIn(?string $ciIn): static
    {
        $this->ciIn = $ciIn;

        return $this;
    }

    public function getCiTin(): ?string
    {
        return $this->ciTin;
    }

    public function setCiTin(?string $ciTin): static
    {
        $this->ciTin = $ciTin;

        return $this;
    }

    public function getCiVatPayer(): ?int
    {
        return $this->ciVatPayer;
    }

    public function setCiVatPayer(?int $ciVatPayer): static
    {
        $this->ciVatPayer = $ciVatPayer;

        return $this;
    }
    
    public function getCiBa(): ?string
    {
        return $this->ciBa;
    }

    public function setCiBa(?string $ciBa): static
    {
        $this->ciBa = $ciBa;

        return $this;
    }

    public function getCiBc(): ?string
    {
        return $this->ciBc;
    }

    public function setCiBc(?string $ciBc): static
    {
        $this->ciBc = $ciBc;

        return $this;
    }

    public function getCiIban(): ?string
    {
        return $this->ciIban;
    }

    public function setCiIban(?string $ciIban): static
    {
        $this->ciIban = $ciIban;

        return $this;
    }

    public function getCiSwift(): ?string
    {
        return $this->ciSwift;
    }

    public function setCiSwift(?string $ciSwift): static
    {
        $this->ciSwift = $ciSwift;

        return $this;
    }

    public function getCiRegisteredRegister(): ?string
    {
        return $this->ciRegisteredRegister;
    }

    public function setCiRegisteredRegister(?string $ciRegisteredRegister): static
    {
        $this->ciRegisteredRegister = $ciRegisteredRegister;

        return $this;
    }

    public function getCiRegisteredOffice(): ?string
    {
        return $this->ciRegisteredOffice;
    }

    public function setCiRegisteredOffice(?string $ciRegisteredOffice): static
    {
        $this->ciRegisteredOffice = $ciRegisteredOffice;

        return $this;
    }

    public function getCiRegisteredCity(): ?string
    {
        return $this->ciRegisteredCity;
    }

    public function setCiRegisteredCity(?string $ciRegisteredCity): static
    {
        $this->ciRegisteredCity = $ciRegisteredCity;

        return $this;
    }

    public function getCiRegisteredFileNumber(): ?string
    {
        return $this->ciRegisteredFileNumber;
    }

    public function setCiRegisteredFileNumber(?string $ciRegisteredFileNumber): static
    {
        $this->ciRegisteredFileNumber = $ciRegisteredFileNumber;

        return $this;
    }

    public function getCiDuns(): ?string
    {
        return $this->ciDuns;
    }

    public function setCiDuns(?string $ciDuns): static
    {
        $this->ciDuns = $ciDuns;

        return $this;
    }

    public function getCiDataBox(): ?string
    {
        return $this->ciDataBox;
    }

    public function setCiDataBox(?string $ciDataBox): static
    {
        $this->ciDataBox = $ciDataBox;

        return $this;
    }

    public function getCiWeb(): ?string
    {
        return $this->ciWeb;
    }

    public function setCiWeb(?string $ciWeb): static
    {
        $this->ciWeb = $ciWeb;

        return $this;
    }

    public function getCiPhoneCode(): ?string
    {
        return $this->ciPhoneCode;
    }

    public function setCiPhoneCode(?string $ciPhoneCode): static
    {
        $this->ciPhoneCode = $ciPhoneCode;

        return $this;
    }
    
    public function getCiPhone(): ?string
    {
        return $this->ciPhone;
    }

    public function setCiPhone(?string $ciPhone): static
    {
        $this->ciPhone = $ciPhone;

        return $this;
    }

    public function getCiMobile(): ?string
    {
        return $this->ciMobile;
    }

    public function setCiMobile(?string $ciMobile): static
    {
        $this->ciMobile = $ciMobile;

        return $this;
    }

    public function getCiFax(): ?string
    {
        return $this->ciFax;
    }

    public function setCiFax(?string $ciFax): static
    {
        $this->ciFax = $ciFax;

        return $this;
    }

    public function getCiEmail(): ?string
    {
        return $this->ciEmail;
    }

    public function setCiEmail(?string $ciEmail): static
    {
        $this->ciEmail = $ciEmail;

        return $this;
    }

    public function getBiName(): ?string
    {
        return $this->biName;
    }

    public function setBiName(?string $biName): static
    {
        $this->biName = $biName;

        return $this;
    }

    public function getBiStreet(): ?string
    {
        return $this->biStreet;
    }

    public function setBiStreet(?string $biStreet): static
    {
        $this->biStreet = $biStreet;

        return $this;
    }

    public function getBiCity(): ?string
    {
        return $this->biCity;
    }

    public function setBiCity(?string $biCity): static
    {
        $this->biCity = $biCity;

        return $this;
    }

    public function getBiZipcode(): ?string
    {
        return $this->biZipcode;
    }

    public function setBiZipcode(?string $biZipcode): static
    {
        $this->biZipcode = $biZipcode;

        return $this;
    }

    public function getBiCountry(): ?string
    {
        return $this->biCountry;
    }

    public function setBiCountry(?string $biCountry): static
    {
        $this->biCountry = $biCountry;

        return $this;
    }

    public function getDiName(): ?string
    {
        return $this->diName;
    }

    public function setDiName(?string $diName): static
    {
        $this->diName = $diName;

        return $this;
    }

    public function getDiStreet(): ?string
    {
        return $this->diStreet;
    }

    public function setDiStreet(?string $diStreet): static
    {
        $this->diStreet = $diStreet;

        return $this;
    }

    public function getDiCity(): ?string
    {
        return $this->diCity;
    }

    public function setDiCity(?string $diCity): static
    {
        $this->diCity = $diCity;

        return $this;
    }

    public function getDiZipcode(): ?string
    {
        return $this->diZipcode;
    }

    public function setDiZipcode(?string $diZipcode): static
    {
        $this->diZipcode = $diZipcode;

        return $this;
    }

    public function getDiCountry(): ?string
    {
        return $this->diCountry;
    }

    public function setDiCountry(?string $diCountry): static
    {
        $this->diCountry = $diCountry;

        return $this;
    }

    public function getDiscord(): ?string
    {
        return $this->discord;
    }

    public function setDiscord(?string $discord): static
    {
        $this->discord = $discord;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): static
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(?string $instagram): static
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getLinkedin(): ?string
    {
        return $this->linkedin;
    }

    public function setLinkedin(?string $linkedin): static
    {
        $this->linkedin = $linkedin;

        return $this;
    }

    public function getPinterest(): ?string
    {
        return $this->pinterest;
    }

    public function setPinterest(?string $pinterest): static
    {
        $this->pinterest = $pinterest;

        return $this;
    }

    public function getSnapchat(): ?string
    {
        return $this->snapchat;
    }

    public function setSnapchat(?string $snapchat): static
    {
        $this->snapchat = $snapchat;

        return $this;
    }

    public function getTelegram(): ?string
    {
        return $this->telegram;
    }

    public function setTelegram(?string $telegram): static
    {
        $this->telegram = $telegram;

        return $this;
    }

    public function getTiktok(): ?string
    {
        return $this->tiktok;
    }

    public function setTiktok(?string $tiktok): static
    {
        $this->tiktok = $tiktok;

        return $this;
    }

    public function getTumblr(): ?string
    {
        return $this->tumblr;
    }

    public function setTumblr(?string $tumblr): static
    {
        $this->tumblr = $tumblr;

        return $this;
    }

    public function getX(): ?string
    {
        return $this->x;
    }

    public function setX(?string $x): static
    {
        $this->x = $x;

        return $this;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function setWhatsapp(?string $whatsapp): static
    {
        $this->whatsapp = $whatsapp;

        return $this;
    }

    public function getYoutube(): ?string
    {
        return $this->youtube;
    }

    public function setYoutube(?string $youtube): static
    {
        $this->youtube = $youtube;

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

    public function getMaintenance(): ?int
    {
        return $this->maintenance;
    }

    public function setMaintenance(?int $maintenance): static
    {
        $this->maintenance = $maintenance;

        return $this;
    }

    public function getMaintenanceText(): ?string
    {
        return $this->maintenanceText;
    }

    public function setMaintenanceText(?string $maintenanceText): static
    {
        $this->maintenanceText = $maintenanceText;

        return $this;
    }

    /**
     * @return Collection<int, ProjectApi>
     */
    public function getProjectProjectApis(): Collection
    {
        return $this->projectProjectApis;
    }

    public function addProjectProjectApi(ProjectApi $projectProjectApi): static
    {
        if (!$this->projectProjectApis->contains($projectProjectApi)) {
            $this->projectProjectApis->add($projectProjectApi);
            $projectProjectApi->setProject($this);
        }

        return $this;
    }

    public function removeProjectProjectApi(ProjectApi $projectProjectApi): static
    {
        if ($this->projectProjectApis->removeElement($projectProjectApi)) {
            // set the owning side to null (unless already changed)
            if ($projectProjectApi->getProject() === $this) {
                $projectProjectApi->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ProjectContact>
     */
    public function getProjectProjectContacts(): Collection
    {
        return $this->projectProjectContacts;
    }

    public function addProjectProjectContact(ProjectContact $projectProjectContact): static
    {
        if (!$this->projectProjectContacts->contains($projectProjectContact)) {
            $this->projectProjectContacts->add($projectProjectContact);
            $projectProjectContact->setProject($this);
        }

        return $this;
    }

    public function removeProjectProjectContact(ProjectContact $projectProjectContact): static
    {
        if ($this->projectProjectContacts->removeElement($projectProjectContact)) {
            // set the owning side to null (unless already changed)
            if ($projectProjectContact->getProject() === $this) {
                $projectProjectContact->setProject(null);
            }
        }

        return $this;
    }

    public function getProjectProjectText(): ?ProjectText
    {
        return $this->projectProjectText;
    }

    public function setProjectProjectText(ProjectText $projectProjectText): static
    {
        // set the owning side of the relation if necessary
        if ($projectProjectText->getProject() !== $this) {
            $projectProjectText->setProject($this);
        }

        $this->projectProjectText = $projectProjectText;

        return $this;
    }

    public function getProjectProjectCookieConsent(): ?ProjectCookieConsent
    {
        return $this->projectProjectCookieConsent;
    }

    public function setProjectProjectCookieConsent(ProjectCookieConsent $projectProjectCookieConsent): static
    {
        // set the owning side of the relation if necessary
        if ($projectProjectCookieConsent->getProject() !== $this) {
            $projectProjectCookieConsent->setProject($this);
        }

        $this->projectProjectCookieConsent = $projectProjectCookieConsent;

        return $this;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getProjectArticles(): Collection
    {
        return $this->projectArticles;
    }

    public function addProjectArticle(Article $article): static
    {
        if (!$this->projectArticles->contains($article)) {
            $this->projectArticles[] = $article;
            $article->setProject($this);
        }
        return $this;
    }

    public function removeProjectArticle(Article $article): static
    {
        if ($this->projectArticles->removeElement($article)) {
            if ($article->getProject() === $this) {
                $article->setProject(null);
            }
        }
        return $this;
    }
}
