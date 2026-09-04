<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\ProcessImportAction;
use App\Enums\ImportStatusEnum;
use App\Enums\QueueEnum;
use App\Models\Import;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

final class ProcessImportJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 3600;

    public function __construct(public readonly Import $import)
    {
        $this->queue = QueueEnum::Imports->value;
    }

    public function uniqueId(): string
    {
        return hash('sha256', sprintf('%s:%s', $this->import->supplier_id, $this->import->external_import_id));
    }

    public function handle(ProcessImportAction $action): void
    {
        $action->handle($this->import);
    }

    public function failed(?Throwable $exception): void
    {
        $this->import->update([
            'status' => ImportStatusEnum::Failed,
            'error' => $exception?->getMessage(),
        ]);
    }
}
