<?php

namespace App\Jobs;

use App\Models\Design;
use App\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenderExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $designId,
        public readonly string $format,
    ) {
    }

    public function handle(ExportService $exportService): void
    {
        $design = Design::findOrFail($this->designId);
        $exportService->render($design, $this->format);
    }
}
