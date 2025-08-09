<?php

namespace App\Service\Web\Search;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO s parametry vyhledávání poskytovatelů.
 */
class ProviderSearchQuery
{
    #[Assert\NotNull] #[Assert\Range(min: -90, max: 90)]
    private float $lat;

    #[Assert\NotNull] #[Assert\Range(min: -180, max: 180)]
    private float $lng;

    #[Assert\Range(min: 0, max: 2000)]
    private ?int $maxDistanceKm = null;

    /** @var int[] */
    private array $categoryIds = [];

    /** @var array<string, mixed> */
    private array $attributeFilters = [];

    #[Assert\Range(min: 1, max: 500)]
    private int $limit = 50;

    #[Assert\Range(min: 0, max: 100000)]
    private int $offset = 0;

    public function __construct(float $lat, float $lng) { $this->lat = $lat; $this->lng = $lng; }

    public function getLat(): float { return $this->lat; }
    public function setLat(float $lat): self { $this->lat = $lat; return $this; }

    public function getLng(): float { return $this->lng; }
    public function setLng(float $lng): self { $this->lng = $lng; return $this; }

    public function getMaxDistanceKm(): ?int { return $this->maxDistanceKm; }
    public function setMaxDistanceKm(?int $km): self { $this->maxDistanceKm = $km; return $this; }

    /** @return int[] */
    public function getCategoryIds(): array { return $this->categoryIds; }
    /** @param int[] $ids */
    public function setCategoryIds(array $ids): self { $this->categoryIds = array_values(array_unique(array_map('intval', $ids))); return $this; }

    /** @return array<string, mixed> */
    public function getAttributeFilters(): array { return $this->attributeFilters; }
    /** @param array<string, mixed> $filters */
    public function setAttributeFilters(array $filters): self { $this->attributeFilters = $filters; return $this; }

    public function addFilters(AttributeFilters $filters): self
    {
        $arr = $filters->toArray();
        $this->attributeFilters = array_merge($this->attributeFilters, $arr);
        return $this;
    }

    public function getLimit(): int { return $this->limit; }
    public function setLimit(int $limit): self { $this->limit = $limit; return $this; }

    public function getOffset(): int { return $this->offset; }
    public function setOffset(int $offset): self { $this->offset = $offset; return $this; }
}
