<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\SearchPropertiesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchPropertiesRequest;
use App\Http\Resources\PropertyResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class PropertyController extends Controller
{
    #[OA\Get(
        path: '/api/properties',
        summary: 'Search properties by their cheapest currently available offer',
        tags: ['Properties'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/SearchPropertiesCityParameter'),
            new OA\Parameter(ref: '#/components/parameters/SearchPropertiesCheckInParameter'),
            new OA\Parameter(ref: '#/components/parameters/SearchPropertiesCheckOutParameter'),
            new OA\Parameter(ref: '#/components/parameters/SearchPropertiesGuestsParameter'),
            new OA\Parameter(ref: '#/components/parameters/SearchPropertiesPerPageParameter'),
            new OA\Parameter(ref: '#/components/parameters/SearchPropertiesPageParameter'),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Paginated list of matching properties, cheapest offer first',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(
                        ref: '#/components/schemas/PropertyResource')
                    ),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ]),
            ),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Validation error'),
        ],
    )]
    public function index(SearchPropertiesRequest $request, SearchPropertiesAction $action): AnonymousResourceCollection
    {
        return PropertyResource::collection($action->handle($request->makeDTO()));
    }
}
