<?php

namespace App\Services;

use Illuminate\Foundation\Vite;
use Illuminate\Support\HtmlString;

class ViteService extends Vite
{
    /**
     * A theme that builds into its own public/build-{theme} folder loads its
     * entries from there, so building another theme into public/build never
     * replaces its manifest.
     *
     * @param  string|string[]  $entrypoints
     * @param  string|null  $buildDirectory
     */
    public function __invoke($entrypoints, $buildDirectory = null): HtmlString
    {
        if ($buildDirectory === null || $buildDirectory === 'build') {
            $buildDirectory = $this->themeBuildDirectory((array) $entrypoints) ?? $buildDirectory;
        }

        return parent::__invoke($entrypoints, $buildDirectory);
    }

    /**
     * Generate a script tag for the given URL.
     */
    protected function makeScriptTag($url): string
    {
        return sprintf('<script type="module" src="%s" data-turbolinks-eval="false"></script>', $url);
    }

    /**
     * @param  array<int, string>  $entrypoints
     */
    private function themeBuildDirectory(array $entrypoints): ?string
    {
        foreach ($entrypoints as $entrypoint) {
            if (preg_match('#^resources/themes/([a-z0-9_-]+)/#', $entrypoint, $matches) !== 1) {
                continue;
            }

            $directory = 'build-' . $matches[1];

            if (is_file(public_path($directory . '/manifest.json'))) {
                return $directory;
            }
        }

        return null;
    }
}
