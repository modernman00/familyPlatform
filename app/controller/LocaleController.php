<?php

declare(strict_types=1);

namespace App\controller;

use App\classes\I18n;

/**
 * LocaleController
 *
 * Handles on-the-fly language switching for international diaspora users.
 * Enforces strict allowlist validation against directory traversal and LFI.
 */
class LocaleController
{
    /**
     * Switch application language and return user to previous page.
     *
     * @param string $locale Requested locale code (e.g. 'en', 'fr', 'es')
     */
    public function switch(string $locale): void
    {
        $success = I18n::setLocale($locale);

        $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

        if ($isJson) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => $success ? 'success' : 'error',
                'locale' => I18n::getLocale(),
            ]);
            exit;
        }

        // Safe redirect
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // Validate referer is internal
        if (!empty($host) && !empty($referer) && str_contains($referer, $host)) {
            header('Location: ' . $referer);
        } else {
            header('Location: /');
        }
        exit;
    }
}
