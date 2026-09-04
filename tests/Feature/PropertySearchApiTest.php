<?php

declare(strict_types=1);

use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Response;

it('returns the cheapest current offer for each matching property', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $checkIn = '2026-10-10';
    $checkOut = '2026-10-15';
    $futureExpiry = '2026-09-10 23:59:59';

    $supplierA = Supplier::factory()->create();
    $supplierB = Supplier::factory()->create();

    $barcelonaApartment = Property::factory()->create(['city' => 'Barcelona']);
    $barcelonaLoft = Property::factory()->create(['city' => 'Barcelona']);
    $madridApartment = Property::factory()->create(['city' => 'Madrid']);

    Offer::factory()->for($supplierA)->for($barcelonaApartment)
        ->withCheckIn($checkIn)
        ->withCheckOut($checkOut)
        ->withGuests(4)
        ->withPrice(90000)
        ->withAvailableUnits(2)
        ->withExpiresAt($futureExpiry)
        ->create();
    $cheapestApartmentOffer = Offer::factory()->for($supplierB)->for($barcelonaApartment)
        ->withCheckIn($checkIn)
        ->withCheckOut($checkOut)
        ->withGuests(4)
        ->withPrice(70000)
        ->withAvailableUnits(1)
        ->withExpiresAt($futureExpiry)
        ->create();
    Offer::factory()->for($supplierA)->for($barcelonaApartment)
        ->withCheckIn($checkIn)
        ->withCheckOut($checkOut)
        ->withGuests(4)
        ->withPrice(10000)
        ->withAvailableUnits(1)
        ->expired()
        ->create();
    $loftOffer = Offer::factory()->for($supplierA)->for($barcelonaLoft)
        ->withCheckIn($checkIn)
        ->withCheckOut($checkOut)
        ->withGuests(2)
        ->withPrice(80000)
        ->withAvailableUnits(1)
        ->withExpiresAt($futureExpiry)
        ->create();
    Offer::factory()->for($supplierB)->for($barcelonaLoft)
        ->withCheckIn($checkIn)
        ->withCheckOut($checkOut)
        ->withGuests(2)
        ->withPrice(50000)
        ->withExpiresAt($futureExpiry)
        ->soldOut()
        ->create();
    Offer::factory()->for($supplierA)->for($madridApartment)
        ->withCheckIn($checkIn)
        ->withCheckOut($checkOut)
        ->withGuests(2)
        ->withPrice(60000)
        ->withAvailableUnits(1)
        ->withExpiresAt($futureExpiry)
        ->create();

    $response = $this->getJson(route('properties.index', [
        'city' => 'Barcelona',
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => 2,
        'per_page' => 10,
    ]));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJsonPath('meta.per_page', 10);
    $response->assertJsonPath('links.prev', null);

    $data = $response->json('data');

    expect($data)->toHaveCount(2);
    expect($data[0]['code'])->toBe($barcelonaApartment->code);
    expect($data[0]['best_offer']['supplier'])->toBe($supplierB->slug);
    expect($data[0]['best_offer']['price'])->toBe($cheapestApartmentOffer->price);
    expect($data[1]['code'])->toBe($barcelonaLoft->code);
    expect($data[1]['best_offer']['price'])->toBe($loftOffer->price);
});

it('paginates results across pages ordered by price', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $checkIn = '2026-10-10';
    $checkOut = '2026-10-15';
    $supplier = Supplier::factory()->create();

    $prices = collect(range(1, 3))
        ->map(function () use ($supplier, $checkIn, $checkOut) {
            $property = Property::factory()->create(['city' => 'Barcelona']);

            return Offer::factory()->for($supplier)->for($property)
                ->withCheckIn($checkIn)
                ->withCheckOut($checkOut)
                ->withGuests(2)
                ->withAvailableUnits(1)
                ->withExpiresAt('2026-09-10 23:59:59')
                ->create()
                ->price;
        })
        ->sort()
        ->values();

    $page1 = $this->getJson(route('properties.index', [
        'city' => 'Barcelona',
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => 2,
        'per_page' => 2,
        'page' => 1,
    ]));

    $page1->assertStatus(Response::HTTP_OK);
    $page1->assertJsonPath('meta.current_page', 1);
    $page1->assertJsonPath('meta.total', 3);

    $page1Data = $page1->json('data');

    expect($page1Data)->toHaveCount(2);
    expect($page1Data[0]['best_offer']['price'])->toBe($prices[0]);
    expect($page1Data[1]['best_offer']['price'])->toBe($prices[1]);

    $page2 = $this->getJson(route('properties.index', [
        'city' => 'Barcelona',
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => 2,
        'per_page' => 2,
        'page' => 2,
    ]));

    $page2->assertStatus(Response::HTTP_OK);
    $page2->assertJsonPath('meta.current_page', 2);

    $page2Data = $page2->json('data');

    expect($page2Data)->toHaveCount(1);
    expect($page2Data[0]['best_offer']['price'])->toBe($prices[2]);
});

it('validates the search query parameters', function () {
    $response = $this->getJson(route('properties.index'));
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['check_in', 'check_out', 'guests']);

    $response = $this->getJson(route('properties.index', [
        'check_in' => '2026-10-15',
        'check_out' => '2026-10-10',
        'guests' => 2,
    ]));
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['check_out']);
});
