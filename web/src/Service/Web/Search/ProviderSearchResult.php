<?php

namespace App\Service\Web\Search;

/**
 * DTO jednoho řádku výsledku vyhledávání poskytovatelů.
 */
class ProviderSearchResult
{
    private int $id;
    private string $name;
    private ?string $city;
    private float $distanceKm;

    public function __construct(int $id, string $name, ?string $city, float $distanceKm)
    {
        $this->id = $id; $this->name = $name; $this->city = $city; $this->distanceKm = $distanceKm;
    }

    public static function fromRow(array $row): self
    {
        return new self(
            (int)$row['id'],
            (string)$row['name'],
            isset($row['city']) ? (string)$row['city'] : null,
            (float)$row['distance_km']
        );
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCity(): ?string { return $this->city; }
    public function getDistanceKm(): float { return $this->distanceKm; }
}
