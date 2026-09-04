<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Property> */
final class PropertyFactory extends Factory
{
    /**
     * Real cities with a code prefix and a handful of well-known landmarks used to build realistic property
     * names/codes (e.g. "BCN-0001", "Apartment near Sagrada Família").
     *
     * @var array<string, array{prefix: string, landmarks: array<int, string>}>
     */
    private array $cities = [
        'Barcelona' => ['prefix' => 'BCN', 'landmarks' => ['Sagrada Familia', 'Park Güell', 'La Rambla', 'Gothic Quarter', 'Barceloneta Beach']],
        'Madrid' => ['prefix' => 'MAD', 'landmarks' => ['Retiro Park', 'Puerta del Sol', 'Gran Via', 'Plaza Mayor']],
        'Lisbon' => ['prefix' => 'LIS', 'landmarks' => ['Alfama', 'Belém Tower', 'Baixa', 'Chiado']],
        'Paris' => ['prefix' => 'PAR', 'landmarks' => ['Eiffel Tower', 'Le Marais', 'Montmartre', 'Louvre']],
        'Rome' => ['prefix' => 'ROM', 'landmarks' => ['Colosseum', 'Trastevere', 'Vatican', 'Pantheon']],
        'Amsterdam' => ['prefix' => 'AMS', 'landmarks' => ['Jordaan', 'Canal Ring', 'Vondelpark']],
        'Berlin' => ['prefix' => 'BER', 'landmarks' => ['Mitte', 'Kreuzberg', 'Brandenburg Gate']],
        'Prague' => ['prefix' => 'PRG', 'landmarks' => ['Old Town Square', 'Charles Bridge', 'Prague Castle']],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = fake()->randomElement(array_keys($this->cities));
        $meta = $this->cities[$city];
        $type = fake()->randomElement(['Apartment', 'Studio', 'Loft', 'Flat', 'Penthouse']);

        return [
            'code' => sprintf('%s-%s', $meta['prefix'], fake()->unique()->numerify('####')),
            'name' => sprintf('%s near %s', $type, fake()->randomElement($meta['landmarks'])),
            'city' => $city,
        ];
    }
}
