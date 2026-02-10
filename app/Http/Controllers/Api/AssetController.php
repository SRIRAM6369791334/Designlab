<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadAssetRequest;
use App\Models\Design;
use App\Services\AssetService;
use App\Services\VirusScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function store(
        UploadAssetRequest $request,
        VirusScanService $virusScanService,
        AssetService $assetService
    ): JsonResponse {
        $file = $request->file('asset');
        abort_unless($file !== null, 422, 'No file uploaded');

        abort_if(! $virusScanService->isSafe($file), 422, 'Upload failed malware scan');

        $designId = $request->integer('design_id');

        if ($designId) {
            $design = Design::findOrFail($designId);
            $this->authorize('update', $design);
        }

        $asset = $assetService->store($request->user(), $file, $designId);

        return response()->json([
            'id' => $asset->id,
            'optimized_url' => $asset->optimized_path
                ? Storage::disk('public')->url($asset->optimized_path)
                : null,
            'original_url' => Storage::disk('public')->url($asset->original_path),
            'mime_type' => $asset->mime_type,
        ], 201);
    }
}
