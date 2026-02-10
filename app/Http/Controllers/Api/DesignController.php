<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignRequest;
use App\Http\Requests\UpdateDesignRequest;
use App\Models\Design;
use App\Models\DesignVersion;
use App\Services\CanvasSanitizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DesignController extends Controller
{
    public function index(): JsonResponse
    {
        $designs = request()->user()
            ->designs()
            ->latest('updated_at')
            ->paginate(20);

        return response()->json($designs);
    }

    public function store(StoreDesignRequest $request, CanvasSanitizer $sanitizer): JsonResponse
    {
        $this->authorize('create', Design::class);

        $canvas = $sanitizer->sanitize($request->validated('canvas_json'));

        $design = DB::transaction(function () use ($request, $canvas): Design {
            $design = Design::create([
                'user_id' => $request->user()->id,
                'name' => $request->validated('name'),
                'slug' => Str::slug($request->validated('name')) . '-' . Str::lower(Str::random(8)),
                'canvas_json' => $canvas,
                'meta' => $request->validated('meta', []),
                'status' => $request->validated('status', Design::STATUS_DRAFT),
            ]);

            DesignVersion::create([
                'design_id' => $design->id,
                'version_number' => 1,
                'canvas_json' => $canvas,
                'created_by' => $request->user()->id,
                'change_note' => 'Initial draft',
            ]);

            return $design;
        });

        return response()->json($design, 201);
    }

    public function show(Design $design): JsonResponse
    {
        $this->authorize('view', $design);

        return response()->json($design->load('versions'));
    }

    public function update(UpdateDesignRequest $request, Design $design, CanvasSanitizer $sanitizer): JsonResponse
    {
        $this->authorize('update', $design);

        $validated = $request->validated();
        $isAutosave = (bool) ($validated['autosave'] ?? false);

        DB::transaction(function () use ($design, $validated, $sanitizer, $isAutosave, $request): void {
            if (array_key_exists('canvas_json', $validated)) {
                $validated['canvas_json'] = $sanitizer->sanitize($validated['canvas_json']);
            }

            if ($isAutosave) {
                $validated['autosaved_at'] = now();
            }

            $design->fill($validated)->save();

            if (! $isAutosave && isset($validated['canvas_json'])) {
                $latestVersion = (int) $design->versions()->max('version_number');

                $design->versions()->create([
                    'version_number' => $latestVersion + 1,
                    'canvas_json' => $validated['canvas_json'],
                    'created_by' => $request->user()->id,
                    'change_note' => $validated['change_note'] ?? 'Manual save',
                ]);
            }
        });

        return response()->json($design->fresh('versions'));
    }

    public function destroy(Design $design): JsonResponse
    {
        $this->authorize('delete', $design);
        $design->delete();

        return response()->json(status: 204);
    }
}
