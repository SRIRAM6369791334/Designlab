<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RenderExportJob;
use App\Models\Design;
use App\Services\ExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function queue(Request $request, Design $design): JsonResponse
    {
        $this->authorize('view', $design);

        $validated = $request->validate([
            'format' => ['required', 'in:png,svg,pdf'],
        ]);

        RenderExportJob::dispatch($design->id, $validated['format'])
            ->onQueue('exports');

        return response()->json([
            'message' => 'Export queued',
            'design_id' => $design->id,
            'format' => $validated['format'],
        ], 202);
    }

    public function instant(Request $request, Design $design, ExportService $exportService): JsonResponse
    {
        $this->authorize('view', $design);

        $validated = $request->validate([
            'format' => ['required', 'in:png,svg,pdf'],
        ]);

        $result = $exportService->render($design, $validated['format']);

        return response()->json([
            'download_url' => $result['url'],
            'path' => $result['path'],
            'expires_in_seconds' => 900,
        ]);
    }
}
