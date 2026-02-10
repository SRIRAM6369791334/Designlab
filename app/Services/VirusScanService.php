<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class VirusScanService
{
    /**
     * Hook for ClamAV or external malware API.
     */
    public function isSafe(UploadedFile $file): bool
    {
        // Example command: clamscan --stdout --no-summary {path}
        // Keep synchronous checks short; offload deep scans to queue when needed.
        return $file->isValid();
    }
}
