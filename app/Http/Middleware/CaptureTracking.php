<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Remembers where a visitor came from.
 *  - gt_first: set once, never overwritten (first touch), kept 30 days.
 *  - gt_last: refreshed whenever the visit carries ad parameters (latest touch).
 * Both are stored with each enquiry and sent to GoHighLevel.
 */
class CaptureTracking
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethod('GET') || $request->is('admin*') || $request->ajax()) {
            return $response;
        }

        $keys = config('gotour.tracking_keys');
        $params = array_filter($request->only($keys), fn ($v) => is_string($v) && $v !== '');
        $minutes = config('gotour.tracking_days') * 24 * 60;

        $referrer = $request->headers->get('referer');
        $external = $referrer && parse_url($referrer, PHP_URL_HOST) !== $request->getHost() ? $referrer : null;

        $touch = array_merge(array_map(fn ($v) => mb_substr($v, 0, 255), $params), [
            'referrer' => $external ? mb_substr($external, 0, 500) : null,
            'landing_page' => mb_substr($request->fullUrl(), 0, 500),
            'captured_at' => now()->toIso8601String(),
        ]);

        if (! $request->cookie('gt_first')) {
            $response->headers->setCookie(cookie('gt_first', json_encode($touch), $minutes, '/', null, null, true, false, 'lax'));
        }

        if ($params || ! $request->cookie('gt_last')) {
            $response->headers->setCookie(cookie('gt_last', json_encode($touch), $minutes, '/', null, null, true, false, 'lax'));
        }

        return $response;
    }

    public static function read(Request $request, string $name): array
    {
        $raw = $request->cookie($name);
        $data = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($data) ? $data : [];
    }
}
