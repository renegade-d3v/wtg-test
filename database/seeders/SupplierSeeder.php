<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class SupplierSeeder extends Seeder
{
    /**
     * Seed the two suppliers required for the import endpoint (`supplier-a`, `supplier-b`).
     */
    public function run(): void
    {
        $suppliers = [
            ['slug' => 'supplier-a', 'name' => 'Supplier A Rentals'],
            ['slug' => 'supplier-b', 'name' => 'Supplier B Stays'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->updateOrCreate(
                ['slug' => $supplier['slug']],
                ['name' => $supplier['name']],
            );
        }
    }
}
