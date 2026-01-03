<?php
declare(strict_types=1);

class SessionManager {
    /**
     * Starts the session with secure parameters if not already started.
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    /**
     * Starts the session with secure parameters if not already started.
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Session cookie params — must be set before session_start()
            // Allow secure cookies if HTTPS OR if explicitly on localhost for testing (though browsers might block secure cookies on http://localhost)
            // Better approach: Only set secure=true if actually on HTTPS.
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
            
            session_set_cookie_params([
                'lifetime' => 0,          // session cookie
                'path' => '/',
                'domain' => '',           // set if needed for subdomains
                'secure' => $isHttps,     // true only on HTTPS
                'httponly' => true,       // not accessible from JS
                'samesite' => 'Lax'       // Lax is a reasonable default for SPA forms
            ]);
            
            session_start();
        }
    }

    /**
     * Returns the full current logged-in user data or null.
     */
    public static function getCurrentUserData(): ?array {
        self::start();
        if (!isset($_SESSION['username'])) {
            return null;
        }
        
        // Helper to consistently get roles
        $roles = [];
        if (isset($_SESSION['roles']) && is_array($_SESSION['roles'])) {
            $roles = $_SESSION['roles'];
        } elseif (isset($_SESSION['role'])) {
            $roles = [$_SESSION['role']];
        }

        $pic = $_SESSION['profile_picture'] ?? 'assets/public/default.png';
        if (substr($pic, 0, 1) === '/') {
            $pic = substr($pic, 1);
        }

        return [
            'id' => $_SESSION['user_id'] ?? 0,
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'] ?? '',
            'roles' => $roles,
            'loyalty_points' => $_SESSION['loyalty_points'] ?? 0,
            'profile_picture' => $pic
        ];
    }

    /**
     * Checks if a user is logged in.
     */
    public static function isLoggedIn(): bool {
        return self::getCurrentUserData() !== null;
    }

    /**
     * Logs the user in and regenerates the session ID.
     */
    public static function login(array $user): void {
        self::start();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'] ?? '';
        // Handle legacy/single role vs array
        if (isset($user['role'])) {
            $_SESSION['role'] = $user['role'];
            $_SESSION['roles'] = [$user['role']];
        } elseif (isset($user['roles'])) {
             $_SESSION['roles'] = $user['roles'];
             // fallback for single role legacy checks
             $_SESSION['role'] = $user['roles'][0] ?? self::ROLE_USER;
        } else {
            $_SESSION['role'] = self::ROLE_USER;
            $_SESSION['roles'] = [self::ROLE_USER];
        }
        
        // New fields
        $_SESSION['loyalty_points'] = $user['PuncteFidelitate'] ?? 0;
        $_SESSION['profile_picture'] = $user['PPicture'] ?? 'assets/public/default.png';
    }

    /**
     * Logs the user out and potentially destroys the session.
     */
    public static function logout(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
