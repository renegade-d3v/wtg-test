<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateReservationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class ReservationController extends Controller
{
    #[OA\Post(
        path: '/api/offers/{offer}/reservations',
        summary: 'Book an offer',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreReservationRequest'),
        ),
        tags: ['Reservations'],
        parameters: [
            new OA\Parameter(
                name: 'offer', description: 'Offer ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_CREATED,
                description: 'Reservation created',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ReservationResource'),
                ]),
            ),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Offer not found'),
            new OA\Response(response: Response::HTTP_CONFLICT, description: 'Offer has no units available, or client_reference already used for another offer'),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Validation error'),
        ],
    )]
    public function store(StoreReservationRequest $request, Offer $offer, CreateReservationAction $action): JsonResponse
    {
        return ReservationResource::make($action->handle($offer, $request->makeDTO()))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
