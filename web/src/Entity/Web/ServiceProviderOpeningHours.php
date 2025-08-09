<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'service_provider_opening_hours')]
class ServiceProviderOpeningHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServiceProvider::class, inversedBy: 'openingHours')]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Column(name: 'day_of_week', type: 'smallint')]
    #[Assert\Range(min: 0, max: 6)]
    private int $dayOfWeek = 1; // 0=Ne ... 6=So

    #[ORM\Column(name: 'is_closed', type: 'boolean', options: ['default' => 0])]
    private bool $isClosed = false;

    #[ORM\Column(name: 'open_time', type: 'time', nullable: true)]
    private ?\DateTimeInterface $openTime = null;

    #[ORM\Column(name: 'close_time', type: 'time', nullable: true)]
    private ?\DateTimeInterface $closeTime = null;

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $serviceProvider): self { $this->serviceProvider = $serviceProvider; return $this; }

    public function getDayOfWeek(): int { return $this->dayOfWeek; }
    public function setDayOfWeek(int $dayOfWeek): self { $this->dayOfWeek = $dayOfWeek; return $this; }

    public function isClosed(): bool { return $this->isClosed; }
    public function setIsClosed(bool $isClosed): self { $this->isClosed = $isClosed; return $this; }

    public function getOpenTime(): ?\DateTimeInterface { return $this->openTime; }
    public function setOpenTime(?\DateTimeInterface $openTime): self { $this->openTime = $openTime; return $this; }

    public function getCloseTime(): ?\DateTimeInterface { return $this->closeTime; }
    public function setCloseTime(?\DateTimeInterface $closeTime): self { $this->closeTime = $closeTime; return $this; }

    public function __toString(): string
    {
        if ($this->isClosed) return (string)$this->dayOfWeek.' closed';
        return sprintf('%d %s-%s', $this->dayOfWeek, $this->openTime?->format('H:i'), $this->closeTime?->format('H:i'));
    }
}
