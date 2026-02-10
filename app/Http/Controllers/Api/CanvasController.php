<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Design;
use Illuminate\Http\JsonResponse;

class CanvasController extends Controller
{
    public function load(Design $design): JsonResponse
    {
        $this->authorize('view', $design);

        return response()->json([
            'design_id' => $design->id,
            'name' => $design->name,
            'canvas_json' => $design->canvas_json,
            'updated_at' => $design->updated_at,
        ]);
    }
}
