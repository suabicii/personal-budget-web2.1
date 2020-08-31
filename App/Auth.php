<?php

namespace App;

use App\Models\User;

/**
 * Autoryzacja
 * 
 * PHP v 7+
 */
class Auth
{
    /**
     * Logowanie użytkownika
     * 
     * @param User $user  Model "user"
     * 
     * @return void
     */
    public static function login($user, $remember_me)
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
    }

    /**
     * Wyloguj użytkownika
     * 
     * @return void
     */
    public static function logout()
    {
        // Unset all of the session variables.
        $_SESSION = [];

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();

        // static::forgetLogin();
    }
}
