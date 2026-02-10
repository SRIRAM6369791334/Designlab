<?php

namespace App\Services;

use App\Models\Design;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportService
{
    public function render(Design $design, string $format): array
    {
        $supported = ['png', 'svg', 'pdf'];

        if (! in_array($format, $supported, true)) {
            abort(422, 'Unsupported format');
        }

        // For production: use headless Chromium + node-canvas/cairo for precise Fabric rendering at 300 DPI.
        $payload = json_encode($design->canvas_json, JSON_THROW_ON_ERROR);
        $filename = sprintf('%s.%s', Str::uuid(), $format);
        $path = "exports/{$design->user_id}/{$filename}";

        Storage::disk('private')->put($path, $payload);

        return [
            'path' => $path,
            'url' => Storage::disk('private')->temporaryUrl($path, now()->addMinutes(15)),
        ];
    }
}
