<?php

namespace App\Entity\Admin;

use App\Repository\Admin\DocumentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['document_details'])]
    private ?int $recordId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['document_details'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['document_details'])]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['document_details'])]
    private ?string $version = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document_details'])]
    private ?string $fileName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document_details'])]
    private ?string $fileType = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document_details'])]
    private ?string $filePath = null;

    #[ORM\Column(length: 255)]
    #[Groups(['document_details'])]
    private ?string $fileSize = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['document_details'])]
    private ?int $position = null;

    #[ORM\Column]
    #[Groups(['document_details'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Groups(['document_details'])]
    private ?int $publication = null;

    #[ORM\ManyToOne(inversedBy: 'entityDocuments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Entity $entity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecordId(): ?int
    {
        return $this->recordId;
    }

    public function setRecordId(int $recordId): static
    {
        $this->recordId = $recordId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): static
    {
        $this->version = $version;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(string $fileType): static
    {
        $this->fileType = $fileType;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getFileSize(): ?string
    {
        return $this->fileSize;
    }

    public function setFileSize(string $fileSize): static
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

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

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function setEntity(?Entity $entity): static
    {
        $this->entity = $entity;

        return $this;
    }
}
