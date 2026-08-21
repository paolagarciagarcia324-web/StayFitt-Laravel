<?php

namespace App\Support;

use Illuminate\Http\Request;

trait ResolvesAdminMedia
{
    protected function resolveMedia(
        Request $request,
        string $fileField,
        string $folder,
        ?string $urlField = null,
        ?string $fallback = null
    ): ?string {
        if ($request->hasFile($fileField)) {
            return $request->file($fileField)->store($folder, 'public');
        }

        $url = trim((string) ($urlField !== null ? $request->input($urlField, '') : ''));

        return $url !== '' ? $url : $fallback;
    }

    protected function mediaPublicUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
