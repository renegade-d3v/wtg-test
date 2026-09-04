<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\OfferData;
use App\DTOs\PropertyData;
use App\Enums\ImportStatusEnum;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use Illuminate\Support\Facades\DB;
use Throwable;

final class ProcessImportAction
{
    public function handle(Import $import): void
    {
        $import->update([
            'status' => ImportStatusEnum::Processing,
            'total_offers' => count($import->payload),
        ]);

        try {
            DB::transaction(function () use ($import): void {
                foreach ($import->payload as $offer) {
                    $this->processOffer($import, $offer);
                }
            });
        } catch (Throwable $e) {
            $import->update([
                'status' => ImportStatusEnum::Failed,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $import->update([
            'status' => ImportStatusEnum::Completed,
            'processed_offers' => count($import->payload),
            'completed_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $offer
     */
    private function processOffer(Import $import, array $offer): void
    {
        $offerData = $this->makeOfferData($offer);
        $property = $this->upsertProperty($offerData->property);

        $this->upsertOffer($import, $offerData, $property);
    }

    /**
     * @param  array<string, mixed>  $offer
     */
    private function makeOfferData(array $offer): OfferData
    {
        return new OfferData(
            external_id: $offer['external_id'],
            property: new PropertyData(
                code: $offer['property']['code'],
                name: $offer['property']['name'],
                city: $offer['property']['city'],
            ),
            check_in: $offer['check_in'],
            check_out: $offer['check_out'],
            max_guests: $offer['max_guests'],
            price: $offer['price'],
            currency: $offer['currency'],
            available_units: $offer['available_units'],
            expires_at: $offer['expires_at'],
        );
    }

    private function upsertProperty(PropertyData $data): Property
    {
        return Property::query()->updateOrCreate([
            'code' => $data->code,
        ], [
            'name' => $data->name,
            'city' => $data->city,
        ]);
    }

    private function upsertOffer(Import $import, OfferData $data, Property $property): Offer
    {
        return Offer::query()->updateOrCreate([
            'supplier_id' => $import->supplier_id,
            'external_id' => $data->external_id,
        ], [
            'property_id' => $property->id,
            'last_import_id' => $import->id,
            'check_in' => $data->check_in,
            'check_out' => $data->check_out,
            'max_guests' => $data->max_guests,
            'price' => $data->price,
            'currency' => $data->currency,
            'available_units' => $data->available_units,
            'expires_at' => $data->expires_at,
        ]);
    }
}
