<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

final class ReservationSeeder extends Seeder
{
    /**
     * Book roughly a third of the still-available offers, decrementing
     * available_units the same way the reservation endpoint would.
     */
    public function run(): void
    {
        Offer::query()
            ->where('available_units', '>', 0)
            ->where('expires_at', '>', now())
            ->get()
            ->each(function (Offer $offer) {
                if (fake()->boolean(33)) {
                    Reservation::factory()->for($offer)->create();
                    $offer->decrement('available_units');
                }
            });
    }
}
