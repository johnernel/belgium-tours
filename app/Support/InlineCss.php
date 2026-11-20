<?php

namespace App\Support;

/**
 * Returns the built public stylesheet so the layout can inline it in <style>:
 * the page then paints without waiting for a render-blocking CSS request.
 * Returns null while the Vite dev server runs or when the build is missing,
 * and the layout falls back to the normal @vite stylesheet link.
 */
class InlineCss
{
    public static function app(): ?string
    {
        if (is_file(public_path('hot'))) {
            return null;
        }
        $manifest = public_path('build/manifest.json');
        if (! is_file($manifest)) {
            return null;
        }
        $entry = json_decode((string) file_get_contents($manifest), true)['resources/css/app.css']['file'] ?? null;
        $path = $entry ? public_path('build/'.$entry) : null;
        if (! $path || ! is_file($path)) {
            return null;
        }

        return str_replace('</style', '<\/style', (string) file_get_contents($path));
    }
}
