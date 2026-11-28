<?php

namespace App\Http\Controllers\Miscellaneous;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeSwitchController extends Controller
{
    public function __invoke(Request $request, string $theme): RedirectResponse
    {
        abort_unless(in_array($theme, SettingsService::switchableThemes(), true), 404);

        return redirect($this->returnPath($request))
            ->withCookie(cookie(SettingsService::THEME_COOKIE, $theme, 60 * 24 * 365));
    }

    /**
     * Back to the page the visitor came from, as a path on this site without the desktop's embed flag.
     */
    private function returnPath(Request $request): string
    {
        // The Referer is the page the visitor clicked from; the session's previous URL can be a background request.
        $referer = (string) $request->headers->get('referer');
        $previous = $referer !== '' && parse_url($referer, PHP_URL_HOST) === $request->getHost()
            ? (parse_url($referer, PHP_URL_PATH) ?: '/')
            : '/';

        if (! str_starts_with($previous, '/') || str_starts_with($previous, '//') || str_starts_with($previous, '/theme/') || str_starts_with($previous, '/radio/')) {
            return '/';
        }

        return $previous;
    }
}
