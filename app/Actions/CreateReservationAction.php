<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\ReservationData;
use App\Enums\ReservationStatusEnum;
use App\Models\Offer;
use App\Models\Reservation;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class CreateReservationAction
{
    public function handle(Offer $offer, ReservationData $data): Reservation
    {
        $existing = Reservation::query()
            ->where('client_reference', $data->client_reference)
            ->first();

        if ($existing) {
            throw_if(
                $existing->offer_id !== $offer->id,
                $this->conflict('Client reference already used for another offer.')
            );

            return $existing;
        }

        try {
            return DB::transaction(fn (): Reservation => $this->createReservation($offer, $data));
        } catch (UniqueConstraintViolationException) {
            return Reservation::query()
                ->where('client_reference', $data->client_reference)
                ->where('offer_id', $offer->id)
                ->first() ?? throw $this->conflict('Client reference already used for another offer.');
        }
    }

    private function createReservation(Offer $offer, ReservationData $data): Reservation
    {
        $decremented = Offer::query()
            ->whereKey($offer->id)
            ->where('available_units', '>', 0)
            ->where('expires_at', '>', now())
            ->decrement('available_units');

        throw_if($decremented === 0, $this->conflict('Offer is no longer available.'));

        return Reservation::query()->create([
            'offer_id' => $offer->id,
            'client_reference' => $data->client_reference,
            'customer_name' => $data->customer_name,
            'customer_email' => $data->customer_email,
            'status' => ReservationStatusEnum::Confirmed,
        ]);
    }

    private function conflict(string $message): HttpException
    {
        return new HttpException(Response::HTTP_CONFLICT, $message);
    }
}
