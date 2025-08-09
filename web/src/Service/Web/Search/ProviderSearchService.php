<?php

namespace App\Service\Web\Search;

use App\Repository\Web\ServiceProviderRepository;

/**
 * Fasáda nad ServiceProviderRepository pro jednodušší použití v controllerech.
 */
class ProviderSearchService
{
    public function __construct(private readonly ServiceProviderRepository $repo) {}

    /** @return ProviderSearchResult[] */
    public function search(ProviderSearchQuery $q): array
    {
        $rows = $this->repo->searchByDistanceAndAttributes(
            lat: $q->getLat(),
            lng: $q->getLng(),
            maxDistanceKm: $q->getMaxDistanceKm(),
            categoryIds: $q->getCategoryIds(),
            attributeFilters: $q->getAttributeFilters(),
            limit: $q->getLimit(),
            offset: $q->getOffset()
        );

        $out = [];
        foreach ($rows as $r) { $out[] = ProviderSearchResult::fromRow($r); }
        return $out;
    }
}
