<?php

declare(strict_types=1);

use App\Models\Offer;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Supplier;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Response;

function reservationPayload(): array
{
    $offer = Offer::factory()
        ->for(Supplier::factory()->make())
        ->for(Property::factory()->make())
        ->make();

    $reservation = Reservation::factory()->for($offer)->make();

    return [
        'client_reference' => $reservation->client_reference,
        'customer_name' => $reservation->customer_name,
        'customer_email' => $reservation->customer_email,
    ];
}

function reservationsRoute(int $offerId): string
{
    return route('offers.reservations.store', ['offer' => $offerId]);
}

it('creates a reservation and decrements available units', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $offer = Offer::factory()->withAvailableUnits(2)->withExpiresAt('2026-09-10 23:59:59')->create();

    $payload = reservationPayload();

    $response = $this->postJson(reservationsRoute($offer->id), $payload);
    $response->assertStatus(Response::HTTP_CREATED);
    $response->assertJsonPath('data.status', 'confirmed');

    expect($offer->refresh()->available_units)->toBe(1);

    $this->assertDatabaseHas('reservations', [
        'offer_id' => $offer->id,
        'client_reference' => $payload['client_reference'],
        'customer_email' => $payload['customer_email'],
    ]);
});

it('rejects a second reservation for the last available unit', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $offer = Offer::factory()->withAvailableUnits(1)->withExpiresAt('2026-09-10 23:59:59')->create();

    $first = $this->postJson(reservationsRoute($offer->id), reservationPayload());
    $first->assertStatus(Response::HTTP_CREATED);

    $second = $this->postJson(reservationsRoute($offer->id), reservationPayload());
    $second->assertStatus(Response::HTTP_CONFLICT);

    expect($offer->refresh()->available_units)->toBe(0);
    $this->assertDatabaseCount('reservations', 1);
});

it('rejects expired offers without decrementing available units', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $offer = Offer::factory()->withAvailableUnits(1)->withExpiresAt('2026-09-03 23:59:59')->create();

    $response = $this->postJson(reservationsRoute($offer->id), reservationPayload());
    $response->assertStatus(Response::HTTP_CONFLICT);

    expect($offer->refresh()->available_units)->toBe(1);
    $this->assertDatabaseCount('reservations', 0);
});

it('replays the same reservation for a repeated client_reference without double-booking', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $offer = Offer::factory()->withAvailableUnits(2)->withExpiresAt('2026-09-10 23:59:59')->create();

    $payload = reservationPayload();

    $first = $this->postJson(reservationsRoute($offer->id), $payload);
    $first->assertStatus(Response::HTTP_CREATED);

    $second = $this->postJson(reservationsRoute($offer->id), $payload);
    $second->assertStatus(Response::HTTP_CREATED);

    expect($second->json('data.id'))->toBe($first->json('data.id'))
        ->and($offer->refresh()->available_units)->toBe(1);

    $this->assertDatabaseCount('reservations', 1);
});

it('rejects a client_reference already used for a different offer', function () {
    $this->travelTo(CarbonImmutable::createFromFormat('Y-m-d H:i:s', '2026-09-04 12:00:00'));

    $firstOffer = Offer::factory()->withAvailableUnits(2)->withExpiresAt('2026-09-10 23:59:59')->create();
    $secondOffer = Offer::factory()->withAvailableUnits(2)->withExpiresAt('2026-09-10 23:59:59')->create();

    $sharedReference = reservationPayload()['client_reference'];

    $firstPayload = reservationPayload();
    $firstPayload['client_reference'] = $sharedReference;

    $secondPayload = reservationPayload();
    $secondPayload['client_reference'] = $sharedReference;

    $first = $this->postJson(reservationsRoute($firstOffer->id), $firstPayload);
    $first->assertStatus(Response::HTTP_CREATED);

    $second = $this->postJson(reservationsRoute($secondOffer->id), $secondPayload);
    $second->assertStatus(Response::HTTP_CONFLICT);

    expect($secondOffer->refresh()->available_units)->toBe(2);
    $this->assertDatabaseCount('reservations', 1);
});

it('validates the reservation payload', function () {
    $offer = Offer::factory()->create();

    $response = $this->postJson(reservationsRoute($offer->id), []);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['client_reference', 'customer_name', 'customer_email']);

    $payload = reservationPayload();
    $payload['customer_email'] = 'not-an-email';

    $response = $this->postJson(reservationsRoute($offer->id), $payload);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['customer_email']);
});
