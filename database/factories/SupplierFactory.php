<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Supplier> */
final class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = sprintf(
            '%s %s',
            fake()->unique()->company(),
            fake()->randomElement(['Rentals', 'Stays', 'Apartments', 'Homes', 'Holidays', 'Living']),
        );

        return [
            'slug' => Str::slug($name),
            'name' => $name,
        ];
    }
}
