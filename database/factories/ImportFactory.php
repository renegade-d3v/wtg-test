<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ImportStatusEnum;
use App\Models\Import;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**  @extends Factory<Import> */
final class ImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sentAt = fake()->dateTimeBetween('-2 months');
        $totalOffers = fake()->numberBetween(5, 40);

        return [
            'supplier_id' => Supplier::factory(),
            'external_import_id' => sprintf('import-%s-%s', fake()->unique()->date(), fake()->unique()->numerify()),
            'sent_at' => $sentAt,
            'status' => ImportStatusEnum::Completed,
            'total_offers' => $totalOffers,
            'processed_offers' => $totalOffers,
            'payload' => [],
            'error' => null,
            'completed_at' => (clone $sentAt)->modify(sprintf('+%d seconds', fake()->numberBetween(1, 30))),
        ];
    }

    /**
     * Import that failed processing.
     */
    public function failed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => ImportStatusEnum::Failed,
            'processed_offers' => fake()->numberBetween(0, $attributes['total_offers'] ?? 0),
            'error' => fake()->randomElement([
                'Supplier not found.',
                'Invalid property code for offer external_id.',
                'Duplicate external_id within the same import payload.',
            ]),
            'completed_at' => null,
        ]);
    }
}
