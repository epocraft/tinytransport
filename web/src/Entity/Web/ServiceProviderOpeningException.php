<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_provider_opening_exception')]
#[UniqueEntity(fields: ['serviceProvider', 'date'], message: 'Tento den už má nastavenou výjimku.')]
class ServiceProviderOpeningException
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceProvider::class, inversedBy: 'openingExceptions')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    private \DateTimeInterface $date;

    #[ORM\Column(name: 'is_closed', type: 'boolean', options: ['default' => 0])]
    private bool $isClosed = false;

    #[ORM\Column(name: 'open_time', type: 'time', nullable: true)]
    private ?\DateTimeInterface $openTime = null;

    #[ORM\Column(name: 'close_time', type: 'time', nullable: true)]
    private ?\DateTimeInterface $closeTime = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $note = null;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable(date('Y-m-d'));
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getDate(): \DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }

    public function isClosed(): bool { return $this->isClosed; }
    public function setIsClosed(bool $isClosed): self { $this->isClosed = $isClosed; return $this; }

    public function getOpenTime(): ?\DateTimeInterface { return $this->openTime; }
    public function setOpenTime(?\DateTimeInterface $openTime): self { $this->openTime = $openTime; return $this; }

    public function getCloseTime(): ?\DateTimeInterface { return $this->closeTime; }
    public function setCloseTime(?\DateTimeInterface $closeTime): self { $this->closeTime = $closeTime; return $this; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): self { $this->note = $note; return $this; }

    public function __toString(): string
    {
        return 'Exception '.$this->date->format('Y-m-d').($this->isClosed ? ' (closed)' : '');
    }
}
