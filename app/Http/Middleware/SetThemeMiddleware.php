<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Qirolab\Theme\Theme;
use Symfony\Component\HttpFoundation\Response;

class SetThemeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('atom.mode') === 'headless' || $request->is('api/*', 'housekeeping', 'housekeeping/*', 'sanctum/*')) {
            return $next($request);
        }

        $theme = setting('theme');

        if (empty($theme) || $theme === '1') {
            $theme = 'atom';
        }

        Theme::set($theme, config('theme.parent'));

        $this->useCompiledViewsOf($theme);

        return $next($request);
    }

    /**
     * Pages shared by the themes pick up the header and footer of the theme that compiled them first,
     * so each theme keeps its own compiled views.
     */
    private function useCompiledViewsOf(string $theme): void
    {
        $root = rtrim((string) config('view.compiled_root', config('view.compiled')), '/\\');
        $path = $root . '/' . preg_replace('/[^a-z0-9_-]/i', '', $theme);

        if (! is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        config(['view.compiled_root' => $root, 'view.compiled' => $path]);

        // The compiler is usually built before this runs, so it gets the folder directly.
        $compiler = app('blade.compiler');
        (function () use ($path) {
            $this->cachePath = $path;
        })->call($compiler);
    }
}
