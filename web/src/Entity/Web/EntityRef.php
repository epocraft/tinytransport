<?php

namespace App\Entity\Web;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pozn.: Jméno třídy je EntityRef (ne „Entity“), aby se to nepletlo s Doctrine anotací.
 */
#[ORM\Entity]
#[ORM\Table(name: 'entity')]
class EntityRef
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    // např. 'service_provider', 'service_provider_vehicle', 'quote', ...
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name = '';

    public function getId(): ?int { return $this->id; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function __toString(): string
    {
        return $this->name ?: 'Entity#'.$this->id;
    }
}
