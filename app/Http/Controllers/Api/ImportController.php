<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateImportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImportRequest;
use App\Http\Resources\ImportResource;
use App\Http\Resources\ImportStatusResource;
use App\Models\Import;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

final class ImportController extends Controller
{
    #[OA\Post(
        path: '/api/imports',
        summary: 'Queue a new offers import',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/StoreImportRequest'),
        ),
        tags: ['Imports'],
        responses: [
            new OA\Response(
                response: Response::HTTP_ACCEPTED,
                description: 'Import accepted and queued for processing',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ImportStatusResource'),
                ]),
            ),
            new OA\Response(response: Response::HTTP_UNPROCESSABLE_ENTITY, description: 'Validation error'),
        ],
    )]
    public function store(StoreImportRequest $request, CreateImportAction $action): JsonResponse
    {
        return ImportStatusResource::make($action->handle($request->makeDTO()))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    #[OA\Get(
        path: '/api/imports/{import}',
        summary: 'Get the current status of an import',
        tags: ['Imports'],
        parameters: [
            new OA\Parameter(
                name: 'import', description: 'Import ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: Response::HTTP_OK,
                description: 'Import found',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/ImportResource'),
                ]),
            ),
            new OA\Response(response: Response::HTTP_NOT_FOUND, description: 'Import not found'),
        ],
    )]
    public function show(Import $import): ImportResource
    {
        return ImportResource::make($import);
    }
}
