<?php

namespace App\Service\Web\Search;

/**
 * Builder na atributové filtry pro ServiceProviderRepository::searchByDistanceAndAttributes().
 *
 * Použití:
 *   $filters = AttributeFilters::create()
 *       ->bool('tail_lift', true)
 *       ->min('capacity_kg', 1200)
 *       ->eq('weekend_work', 1)
 *       ->toArray();
 */
final class AttributeFilters
{
    /** @var array<string, mixed> */
    private array $filters = [];

    public static function create(): self { return new self(); }

    /** Rovnost (číselná/text/boolean) */
    public function eq(string $code, mixed $value): self
    {
        $this->filters[$code] = ['eq' => $value];
        return $this;
    }

    /** Boolean helper (true/false) */
    public function bool(string $code, bool $value = true): self
    {
        $this->filters[$code] = $value ? 1 : 0;
        return $this;
    }

    /** Minimální hodnota (číslo) */
    public function min(string $code, int|float $value): self
    {
        $this->filters[$code] = array_merge($this->filters[$code] ?? [], ['min' => $value]);
        return $this;
    }

    /** Maximální hodnota (číslo) */
    public function max(string $code, int|float $value): self
    {
        $this->filters[$code] = array_merge($this->filters[$code] ?? [], ['max' => $value]);
        return $this;
    }

    /** Rozsah (číslo) */
    public function between(string $code, int|float $min, int|float $max): self
    {
        $this->filters[$code] = array_merge($this->filters[$code] ?? [], ['min' => $min, 'max' => $max]);
        return $this;
    }

    /** Textová rovnost (jen sugar pro eq) */
    public function textEq(string $code, string $value): self
    {
        $this->filters[$code] = ['eq' => $value];
        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array { return $this->filters; }
}
