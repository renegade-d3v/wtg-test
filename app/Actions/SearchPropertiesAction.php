<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\SearchPropertiesData;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class SearchPropertiesAction
{
    public function handle(SearchPropertiesData $data): LengthAwarePaginator
    {
        return Property::query()
            ->whereHas('offers', fn (Builder $query) => $this->applyOfferFilters($query, $data))
            ->when($data->city, fn (Builder $query, string $city) => $query->where('city', $city))
            ->addSelect(['best_offer_id' => $this->cheapestMatchingOffer($data)->select('id')])
            ->with('bestOffer.supplier')
            ->orderBy($this->cheapestMatchingOffer($data)->select('price'))
            ->orderBy('properties.id')
            ->paginate(perPage: $data->pagination->perPage, page: $data->pagination->page)
            ->withQueryString();
    }

    private function cheapestMatchingOffer(SearchPropertiesData $data): Builder
    {
        return $this->applyOfferFilters(Offer::query(), $data)
            ->whereColumn('property_id', 'properties.id')
            ->orderBy('price')
            ->orderBy('id')
            ->limit(1);
    }

    private function applyOfferFilters(Builder $query, SearchPropertiesData $data): Builder
    {
        return $query
            ->where('check_in', $data->checkIn)
            ->where('check_out', $data->checkOut)
            ->where('max_guests', '>=', $data->guests)
            ->where('available_units', '>', 0)
            ->where('expires_at', '>', now());
    }
}
