<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Offer> */
final class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('+1 day', '+6 months');
        $nights = fake()->numberBetween(1, 14);
        $checkOut = (clone $checkIn)->modify(sprintf('+%d days', $nights));
        $priceInCents = fake()->numberBetween(4000, 30000);

        return [
            'supplier_id' => Supplier::factory(),
            'property_id' => Property::factory(),
            'external_id' => sprintf('offer-%s', fake()->unique()->bothify('??######')),
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'max_guests' => fake()->numberBetween(1, 6),
            'price' => $priceInCents * $nights,
            'currency' => fake()->randomElement(['EUR', 'USD', 'GBP']),
            'available_units' => fake()->numberBetween(1, 5),
            'expires_at' => fake()->dateTimeBetween('+1 day', '+3 months'),
        ];
    }

    /**
     * Offer with no units left to book.
     */
    public function soldOut(): self
    {
        return $this->state(fn (array $attributes) => [
            'available_units' => 0,
        ]);
    }

    /**
     * Offer whose validity window has already passed.
     */
    public function expired(): self
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }
}
