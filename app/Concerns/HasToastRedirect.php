<?php

namespace App\Concerns;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

trait HasToastRedirect
{
    /**
     * Flash a toast message to Inertia and return a redirect response.
     */
    public static function toast(string $message, string $type = 'success', ?string $redirectTo = null): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => $type,
            'message' => $message,
        ]);

        return $redirectTo !== null ? redirect()->to($redirectTo) : redirect()->back();
    }

    public static function success(string $message, ?string $redirectTo = null): RedirectResponse
    {
        return static::toast($message, 'success', $redirectTo);
    }

    public static function error(string $message, ?string $redirectTo = null): RedirectResponse
    {
        return static::toast($message, 'error', $redirectTo);
    }

    public static function warning(string $message, ?string $redirectTo = null): RedirectResponse
    {
        return static::toast($message, 'warning', $redirectTo);
    }

    public static function info(string $message, ?string $redirectTo = null): RedirectResponse
    {
        return static::toast($message, 'info', $redirectTo);
    }
}
