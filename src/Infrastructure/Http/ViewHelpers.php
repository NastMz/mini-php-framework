<?php
declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Security\CsrfTokenManager;

/**
 * ViewHelpers
 *
 * Provides helper methods for generating HTML elements related to CSRF protection.
 */
class ViewHelpers
{
    /**
     * Returns the HTML for a hidden CSRF input field.
     */
    public static function csrfField(): string
    {
        $token = CsrfTokenManager::getToken();
        return "<input type=\"hidden\" name=\"_csrf_token\" value=\"{$token}\">";
    }

    /**
     * Returns the raw CSRF token string.
     */
    public static function csrfToken(): string
    {
        return CsrfTokenManager::getToken();
    }
}
