<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

final class PropertySeeder extends Seeder
{
    /**
     * Seed a catalogue of properties across a handful of real cities.
     */
    public function run(): void
    {
        Property::factory()->count(15)->create();
    }
}
