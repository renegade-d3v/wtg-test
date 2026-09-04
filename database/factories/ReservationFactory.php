<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ReservationStatusEnum;
use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Reservation> */
final class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'client_reference' => sprintf('web-order-%s', fake()->unique()->uuid()),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'status' => ReservationStatusEnum::Confirmed,
        ];
    }
}
