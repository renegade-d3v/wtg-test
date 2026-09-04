<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\ImportData;
use App\Enums\ImportStatusEnum;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\Supplier;

final class CreateImportAction
{
    public function handle(ImportData $data): Import
    {
        $supplier = Supplier::query()->where('slug', $data->supplier)->firstOrFail();

        $import = Import::query()->createOrFirst([
            'supplier_id' => $supplier->id,
            'external_import_id' => $data->external_import_id,
        ], [
            'sent_at' => $data->sent_at,
            'status' => ImportStatusEnum::Pending,
            'payload' => $data->payload,
        ]);

        if ($import->wasRecentlyCreated) {
            ProcessImportJob::dispatch($import);
        }

        return $import;
    }
}
