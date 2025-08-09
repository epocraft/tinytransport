<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'document')]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // FK: document.entity_id → entity.id (DB nemá FK? ve tvé dump verzi ne; klidně necháme ManyToOne)
    #[ORM\ManyToOne(targetEntity: EntityRef::class)]
    #[ORM\JoinColumn(name: 'entity_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    private EntityRef $entity;

    // record_id – ID z konkrétní tabulky (service_provider.id, quote.id, ...)
    #[ORM\Column(name: 'record_id', type: 'integer')]
    #[Assert\NotNull]
    private int $recordId;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $version = null;

    #[ORM\Column(name: 'file_name', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $fileName = '';

    #[ORM\Column(name: 'file_type', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $fileType = '';

    #[ORM\Column(name: 'file_path', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $filePath = '';

    #[ORM\Column(name: 'file_size', type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private int $fileSize = 0;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $position = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Assert\NotNull]
    private \DateTimeInterface $createdAt;

    // v DB SMALLINT – mapujeme jako integer, ať držíme 1:1 (0/1)
    #[ORM\Column(type: 'smallint')]
    private int $publication = 1;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // --- getters/setters ---

    public function getId(): ?int { return $this->id; }

    public function getEntity(): EntityRef { return $this->entity; }
    public function setEntity(EntityRef $entity): self { $this->entity = $entity; return $this; }

    public function getRecordId(): int { return $this->recordId; }
    public function setRecordId(int $recordId): self { $this->recordId = $recordId; return $this; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): self { $this->name = $name; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getVersion(): ?string { return $this->version; }
    public function setVersion(?string $version): self { $this->version = $version; return $this; }

    public function getFileName(): string { return $this->fileName; }
    public function setFileName(string $fileName): self { $this->fileName = $fileName; return $this; }

    public function getFileType(): string { return $this->fileType; }
    public function setFileType(string $fileType): self { $this->fileType = $fileType; return $this; }

    public function getFilePath(): string { return $this->filePath; }
    public function setFilePath(string $filePath): self { $this->filePath = $filePath; return $this; }

    public function getFileSize(): int { return $this->fileSize; }
    public function setFileSize(int $fileSize): self { $this->fileSize = $fileSize; return $this; }

    public function getPosition(): ?int { return $this->position; }
    public function setPosition(?int $position): self { $this->position = $position; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    public function getPublication(): int { return $this->publication; }
    public function setPublication(int $publication): self { $this->publication = $publication; return $this; }

    public function __toString(): string
    {
        return $this->fileName.' ('.$this->fileType.')';
    }
}
