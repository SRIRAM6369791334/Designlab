<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AssetService
{
    public function store(User $user, UploadedFile $file, ?int $designId = null): Asset
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $hash = hash_file('sha256', $file->getRealPath());

        $originalPath = $file->store("assets/{$user->id}/original", 'public');
        $optimizedPath = $this->optimizeImage($file, $user->id);

        return Asset::create([
            'design_id' => $designId,
            'user_id' => $user->id,
            'original_path' => $originalPath,
            'optimized_path' => $optimizedPath,
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'sha256' => $hash,
            'scan_status' => 'queued',
        ]);
    }

    private function optimizeImage(UploadedFile $file, int $userId): ?string
    {
        if (! str_starts_with($file->getMimeType() ?? '', 'image/')) {
            return null;
        }

        $targetPath = "assets/{$userId}/optimized/" . pathinfo($file->hashName(), PATHINFO_FILENAME) . '.webp';
        $image = imagecreatefromstring((string) file_get_contents($file->getRealPath()));

        if ($image === false) {
            return null;
        }

        ob_start();
        imagewebp($image, null, 82);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($targetPath, $binary, ['visibility' => 'private']);

        return $targetPath;
    }
}
