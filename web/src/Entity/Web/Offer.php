<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table(name: 'offer')]
#[UniqueEntity(fields: ['serviceProvider', 'quote'], message: 'Jeden poskytovatel může poslat jen jednu nabídku na danou poptávku.')]
class Offer
{
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_EXPIRED   = 'expired';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Quote::class, inversedBy: 'offers')]
    #[ORM\JoinColumn(name: 'quote_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Quote $quote;

    #[ORM\ManyToOne(targetEntity: ServiceProvider::class)]
    #[ORM\JoinColumn(name: 'service_provider_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ServiceProvider $serviceProvider;

    #[ORM\Column(name: 'price', type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d+(\.\d{1,2})?$/', message: 'Zadejte částku ve formátu 1234.56')]
    private string $price = '0.00';

    #[ORM\Column(type: 'string', length: 3)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['CZK', 'EUR'])]
    private string $currency = 'CZK';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => self::STATUS_ACTIVE])]
    #[Assert\Choice(choices: [
        self::STATUS_ACTIVE, self::STATUS_WITHDRAWN, self::STATUS_EXPIRED,
        self::STATUS_ACCEPTED, self::STATUS_REJECTED
    ])]
    private string $status = self::STATUS_ACTIVE;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private \DateTimeInterface $createdAt;

    // DB má ON UPDATE CURRENT_TIMESTAMP – pro jistotu nastavíme i v PHP
    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    // pro throttle editací
    #[ORM\Column(name: 'last_edit_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastEditAt = null;

    #[ORM\Column(name: 'accepted_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $acceptedAt = null;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getQuote(): Quote { return $this->quote; }
    public function setQuote(Quote $quote): self { $this->quote = $quote; return $this; }

    public function getServiceProvider(): ServiceProvider { return $this->serviceProvider; }
    public function setServiceProvider(ServiceProvider $sp): self { $this->serviceProvider = $sp; return $this; }

    public function getPrice(): string { return $this->price; }
    public function setPrice(string $price): self { $this->price = $price; return $this; }

    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): self { $this->currency = $currency; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $dt): self { $this->createdAt = $dt; return $this; }

    public function getUpdatedAt(): \DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(\DateTimeInterface $dt): self { $this->updatedAt = $dt; return $this; }

    public function getLastEditAt(): ?\DateTimeInterface { return $this->lastEditAt; }
    public function setLastEditAt(?\DateTimeInterface $dt): self { $this->lastEditAt = $dt; return $this; }

    public function getAcceptedAt(): ?\DateTimeInterface { return $this->acceptedAt; }
    public function setAcceptedAt(?\DateTimeInterface $dt): self { $this->acceptedAt = $dt; return $this; }

    public function __toString(): string
    {
        return sprintf('#%d %s %s', $this->id, $this->price, $this->currency);
    }
}
