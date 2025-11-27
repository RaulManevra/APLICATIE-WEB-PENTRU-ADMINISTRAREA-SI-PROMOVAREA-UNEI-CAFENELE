<?php
declare(strict_types=1);

class SessionManager {
    /**
     * Starts the session with secure parameters if not already started.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Session cookie params — must be set before session_start()
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
            
            session_set_cookie_params([
                'lifetime' => 0,          // session cookie
                'path' => '/',
                'domain' => '',           // set if needed for subdomains
                'secure' => $secure,      // true only on HTTPS
                'httponly' => true,       // not accessible from JS
                'samesite' => 'Lax'       // Lax is a reasonable default for SPA forms
            ]);
            
            session_start();
        }
    }

    /**
     * Returns the current logged-in username or null.
     */
    public static function getCurrentUser(): ?string {
        self::start();
        return $_SESSION['username'] ?? null;
    }

    /**
     * Checks if a user is logged in.
     */
    public static function isLoggedIn(): bool {
        return self::getCurrentUser() !== null;
    }
}
