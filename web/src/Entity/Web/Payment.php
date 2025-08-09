<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'payment')]
class Payment
{
    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    public const STATUS_PENDING  = 'pending';
    public const STATUS_SETTLED  = 'settled';
    public const STATUS_FAILED   = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // FK: payment.unlock_id → offer_unlock.id (ON DELETE CASCADE)
    #[ORM\ManyToOne(targetEntity: OfferUnlock::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'unlock_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private OfferUnlock $unlock;

    #[ORM\Column(type: 'string', length: 32, options: ['default' => self::METHOD_BANK_TRANSFER])]
    #[Assert\Choice(choices: [self::METHOD_BANK_TRANSFER])]
    private string $method = self::METHOD_BANK_TRANSFER;

    #[ORM\Column(type: 'string', length: 16, options: ['default' => self::STATUS_PENDING])]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_SETTLED, self::STATUS_FAILED, self::STATUS_REFUNDED])]
    private string $status = self::STATUS_PENDING;

    // DECIMAL(10,2) → string
    #[ORM\Column(name: 'amount_total', type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Positive]
    private string $amountTotal = '0.00';

    #[ORM\Column(name: 'amount_vat', type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private string $amountVat = '0.00';

    #[ORM\Column(type: 'string', length: 3, options: ['default' => 'CZK'])]
    #[Assert\Currency]
    private string $currency = 'CZK';

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $vs = '';

    #[ORM\Column(name: 'bank_account', type: 'string', length: 64)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    private string $bankAccount = '';

    #[ORM\Column(name: 'bank_account_name', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $bankAccountName = '';

    #[ORM\Column(type: 'string', length: 140, nullable: true)]
    #[Assert\Length(max: 140)]
    private ?string $message = null;

    #[ORM\Column(name: 'qr_payload', type: 'text', nullable: true)]
    private ?string $qrPayload = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'paid_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $paidAt = null;

    #[ORM\Column(name: 'tx_id', type: 'string', length: 64, nullable: true)]
    #[Assert\Length(max: 64)]
    private ?string $txId = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getUnlock(): OfferUnlock { return $this->unlock; }
    public function setUnlock(OfferUnlock $unlock): self { $this->unlock = $unlock; return $this; }

    public function getMethod(): string { return $this->method; }
    public function setMethod(string $method): self { $this->method = $method; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    public function getAmountTotal(): string { return $this->amountTotal; }
    public function setAmountTotal(string $amountTotal): self { $this->amountTotal = $amountTotal; return $this; }

    public function getAmountVat(): string { return $this->amountVat; }
    public function setAmountVat(string $amountVat): self { $this->amountVat = $amountVat; return $this; }

    public function getCurrency(): string { return $this->currency; }
    public function setCurrency(string $currency): self { $this->currency = $currency; return $this; }

    public function getVs(): string { return $this->vs; }
    public function setVs(string $vs): self { $this->vs = $vs; return $this; }

    public function getBankAccount(): string { return $this->bankAccount; }
    public function setBankAccount(string $bankAccount): self { $this->bankAccount = $bankAccount; return $this; }

    public function getBankAccountName(): string { return $this->bankAccountName; }
    public function setBankAccountName(string $bankAccountName): self { $this->bankAccountName = $bankAccountName; return $this; }

    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): self { $this->message = $message; return $this; }

    public function getQrPayload(): ?string { return $this->qrPayload; }
    public function setQrPayload(?string $qrPayload): self { $this->qrPayload = $qrPayload; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getPaidAt(): ?\DateTimeInterface { return $this->paidAt; }
    public function setPaidAt(?\DateTimeInterface $paidAt): self { $this->paidAt = $paidAt; return $this; }

    public function getTxId(): ?string { return $this->txId; }
    public function setTxId(?string $txId): self { $this->txId = $txId; return $this; }

    // --- convenience ---

    public function isPending(): bool { return $this->status === self::STATUS_PENDING; }
    public function isSettled(): bool { return $this->status === self::STATUS_SETTLED; }
    public function isFailed(): bool { return $this->status === self::STATUS_FAILED; }
    public function isRefunded(): bool { return $this->status === self::STATUS_REFUNDED; }

    public function __toString(): string
    {
        return 'Payment#'.$this->id.' '.$this->amountTotal.' '.$this->currency.' ['.$this->status.']';
    }
}
