<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

final class ImportSeeder extends Seeder
{
    /**
     * Seed a handful of completed imports per supplier, so offers can later
     * be linked to a realistic `last_import_id`.
     */
    public function run(): void
    {
        Supplier::all()->each(function (Supplier $supplier) {
            Import::factory()->for($supplier)->count(3)->create();
        });
    }
}
