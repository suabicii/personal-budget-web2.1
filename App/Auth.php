<?php

namespace App;

use App\Models\RememberedLogin;
use App\Models\User;

/**
 * Uwierzytelnianie
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
        $_SESSION['logged_name'] = $user->name;

        if ($remember_me) {
            if ($user->rememberLogin()) {
                setcookie('remember_me', $user->remember_token, $user->expiry_timestamp, '/');
            }
        }
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

        static::forgetLogin();
    }

    /**
     * Pobierz aktualnie zalogowanego użytkownika z sesji lub cookie
     * 
     * @return mixed  Model "user" lub null, jeśli użytkownik nie jest zalogowany
     */
    public static function getUser()
    {
        if (isset($_SESSION['user_id'])) {
            return User::findByID($_SESSION['user_id']);
        } else {
            return static::loginFromRememberedCookie();
        }
    }

    /**
     * Logowanie przez cookie zapamiętujące zalogowanego użytkownika
     * 
     * @return mixed  Model "user" jeśli znaleziono cookie, w przeciwnym wypadku null
     */
    protected static function loginFromRememberedCookie()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);

            if ($remembered_login && !$remembered_login->hasExpired()) {
                $user = $remembered_login->getUser();

                static::login($user, false);

                return $user;
            }
        }
    }

    /**
     * Zapomnij zapamiętane logowanie, jeśli istnieje
     * 
     * @return void
     */
    protected static function forgetLogin()
    {
        $cookie = $_COOKIE['remember_me'] ?? false;

        if ($cookie) {
            $remembered_login = RememberedLogin::findByToken($cookie);

            if ($remembered_login) {
                $remembered_login->delete();
            }
        }

        setcookie('remember_me', '', time() - 3600, '/'); // set to expire in the pasts
    }
}
