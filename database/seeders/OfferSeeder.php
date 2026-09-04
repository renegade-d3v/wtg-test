<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class OfferSeeder extends Seeder
{
    /**
     * Seed 2-4 offers per property from a random supplier, mixing
     * available/sold-out/expired states for edge-case testing. Requires
     * suppliers, imports and properties to already be seeded.
     */
    public function run(): void
    {
        $suppliers = Supplier::with('imports')->get();

        Property::all()->each(function (Property $property) use ($suppliers) {
            $offersCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $offersCount; ++$i) {
                $supplier = $suppliers->random();
                $state = fake()->randomElement(['available', 'available', 'available', 'sold_out', 'expired']);

                Offer::factory()
                    ->for($supplier)
                    ->for($property)
                    ->when($state === 'sold_out', fn ($factory) => $factory->soldOut())
                    ->when($state === 'expired', fn ($factory) => $factory->expired())
                    ->create([
                        'last_import_id' => $supplier->imports->isNotEmpty()
                            ? $supplier->imports->random()->id
                            : null,
                    ]);
            }
        });
    }
}
