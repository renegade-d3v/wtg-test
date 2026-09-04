<?php

declare(strict_types=1);

use App\Actions\ProcessImportAction;
use App\Enums\ImportStatusEnum;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Offer;
use App\Models\Property;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpFoundation\Response;

function offerPayloadFrom(Property $property, Offer $offer): array
{
    return [
        'external_id' => $offer->external_id,
        'property' => [
            'code' => $property->code,
            'name' => $property->name,
            'city' => $property->city,
        ],
        'check_in' => $offer->check_in->toDateString(),
        'check_out' => $offer->check_out->toDateString(),
        'max_guests' => $offer->max_guests,
        'price' => $offer->price,
        'currency' => $offer->currency,
        'available_units' => $offer->available_units,
        'expires_at' => $offer->expires_at->toISOString(),
    ];
}

it('queues a new import and does not queue duplicate submissions', function () {
    Queue::fake();

    Supplier::factory()->create(['slug' => 'supplier-a']);

    $property = Property::factory()->make();
    $offer = Offer::factory()
        ->for(Supplier::factory()->make())
        ->for(Property::factory()->make())
        ->make();

    $payload = [
        'supplier' => 'supplier-a',
        'external_import_id' => fake()->unique()->bothify('import-####-????'),
        'sent_at' => now()->toISOString(),
        'offers' => [offerPayloadFrom($property, $offer)],
    ];

    $firstResponse = $this->postJson(route('imports.store'), $payload);
    $firstResponse->assertStatus(Response::HTTP_ACCEPTED);
    $firstResponse->assertJsonPath('data.status', 'pending');

    $secondResponse = $this->postJson(route('imports.store'), $payload);
    $secondResponse->assertStatus(Response::HTTP_ACCEPTED);
    $secondResponse->assertJsonPath('data.status', 'pending');

    expect($secondResponse->json('data.id'))->toBe($firstResponse->json('data.id'));

    $this->assertDatabaseCount('imports', 1);
    Queue::assertPushed(ProcessImportJob::class, 1);
});

it('processes imported offers and updates an existing supplier offer', function () {
    $supplier = Supplier::factory()->create();

    $property = Property::factory()->make();
    $offer = Offer::factory()
        ->for(Supplier::factory()->make())
        ->for(Property::factory()->make())
        ->make();

    $firstImport = Import::factory()->for($supplier)->create([
        'status' => ImportStatusEnum::Pending,
        'payload' => [offerPayloadFrom($property, $offer)],
        'total_offers' => 0,
        'processed_offers' => 0,
    ]);

    app(ProcessImportAction::class)->handle($firstImport);

    $this->assertDatabaseHas('properties', [
        'code' => $property->code,
        'name' => $property->name,
        'city' => $property->city,
    ]);

    $this->assertDatabaseHas('offers', [
        'supplier_id' => $supplier->id,
        'external_id' => $offer->external_id,
        'price' => $offer->price,
        'available_units' => $offer->available_units,
    ]);

    expect($firstImport->refresh())
        ->status->toBe(ImportStatusEnum::Completed)
        ->total_offers->toBe(1)
        ->processed_offers->toBe(1);

    $updatedProperty = Property::factory()->make(['code' => $property->code]);
    $updatedOffer = Offer::factory()
        ->for(Supplier::factory()->make())
        ->for(Property::factory()->make())
        ->make()
        ->fill(['external_id' => $offer->external_id]);

    $secondImport = Import::factory()->for($supplier)->create([
        'status' => ImportStatusEnum::Pending,
        'payload' => [offerPayloadFrom($updatedProperty, $updatedOffer)],
        'total_offers' => 0,
        'processed_offers' => 0,
    ]);

    app(ProcessImportAction::class)->handle($secondImport);

    $this->assertDatabaseCount('offers', 1);
    $this->assertDatabaseHas('properties', [
        'code' => $property->code,
        'name' => $updatedProperty->name,
    ]);
    $this->assertDatabaseHas('offers', [
        'supplier_id' => $supplier->id,
        'external_id' => $offer->external_id,
        'last_import_id' => $secondImport->id,
        'price' => $updatedOffer->price,
        'available_units' => $updatedOffer->available_units,
    ]);
});

it('shows the current status of an import', function () {
    $import = Import::factory()->create([
        'status' => ImportStatusEnum::Completed,
        'total_offers' => 20,
        'processed_offers' => 20,
        'error' => null,
    ]);

    $response = $this->getJson(route('imports.show', ['import' => $import->id]));

    $response->assertStatus(Response::HTTP_OK);
    $response->assertJson([
        'data' => [
            'id' => $import->id,
            'supplier' => $import->supplier->slug,
            'external_import_id' => $import->external_import_id,
            'status' => 'completed',
            'total_offers' => 20,
            'processed_offers' => 20,
            'error' => null,
        ],
    ]);
});

it('returns 404 for an unknown import', function () {
    $unknownId = Import::factory()->create()->id + 1;

    $response = $this->getJson(route('imports.show', ['import' => $unknownId]));

    $response->assertStatus(Response::HTTP_NOT_FOUND);
});

it('validates the import payload', function () {
    $response = $this->postJson(route('imports.store'), []);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['supplier', 'external_import_id', 'sent_at', 'offers']);

    $supplier = Supplier::factory()->create();
    $property = Property::factory()->make();
    $offer = Offer::factory()
        ->for(Supplier::factory()->make())
        ->for(Property::factory()->make())
        ->make();

    $payload = [
        'supplier' => "{$supplier->slug}-does-not-exist",
        'external_import_id' => fake()->unique()->bothify('import-####-????'),
        'sent_at' => now()->toISOString(),
        'offers' => [offerPayloadFrom($property, $offer)],
    ];

    $response = $this->postJson(route('imports.store'), $payload);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors(['supplier']);

    $response = $this->postJson(route('imports.store'), [
        'supplier' => $supplier->slug,
        'external_import_id' => fake()->unique()->uuid(),
        'sent_at' => now()->toISOString(),
        'offers' => [
            ['external_id' => fake()->unique()->uuid()],
        ],
    ]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJsonValidationErrors([
        'offers.0.property',
        'offers.0.check_in',
        'offers.0.check_out',
        'offers.0.max_guests',
        'offers.0.price',
        'offers.0.currency',
        'offers.0.expires_at',
    ]);
});

function outOfRangeOfferPayload(): array
{
    $property = Property::factory()->make();
    $offer = Offer::factory()
        ->for(Supplier::factory()->make())
        ->for($property)
        ->withAvailableUnits(999999)
        ->make();

    return [offerPayloadFrom($property, $offer)];
}

it('marks the import as failed and rolls back when processing throws', function () {
    $supplier = Supplier::factory()->create();

    $import = Import::factory()->for($supplier)->create([
        'status' => ImportStatusEnum::Pending,
        'payload' => outOfRangeOfferPayload(),
        'total_offers' => 0,
        'processed_offers' => 0,
    ]);

    (new ProcessImportAction())->handle($import);
})->throws(QueryException::class);

it('records the failure error and leaves no partial rows behind', function () {
    $supplier = Supplier::factory()->create();

    $import = Import::factory()->for($supplier)->create([
        'status' => ImportStatusEnum::Pending,
        'payload' => outOfRangeOfferPayload(),
        'total_offers' => 0,
        'processed_offers' => 0,
    ]);

    rescue(static fn () => (new ProcessImportAction())->handle($import));

    expect($import->refresh())
        ->status->toBe(ImportStatusEnum::Failed)
        ->error->not->toBeNull();

    $this->assertDatabaseCount('properties', 0);
    $this->assertDatabaseCount('offers', 0);
});
