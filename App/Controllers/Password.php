<?php

namespace App\Controllers;

use App\Models\User;
use Core\View;

/**
 * Kontroler do resetowania hasła
 * 
 * PHP v. 7.4
 */
class Password extends \Core\Controller
{
    /**
     * Wyświetl stronę do resetowania hasła
     * 
     * @return void
     */
    public function forgotAction()
    {
        View::renderTemplate('Password/forgot.html');
    }

    /**
     * Wyślij link do resetowania hasła na przesłany przez formularz adres e-mail
     * 
     * @return void
     */
    public function requestResetAction()
    {
        User::sendPasswordReset($_POST['email']);

        View::renderTemplate('Password/reset_requested.html');
    }

    /**
     * Wyświetl formularz do ustawiania nowego hasła
     * 
     * @return void
     */
    public function resetAction()
    {
        $token = $this->route_params['token'];

        View::renderTemplate('Password/reset.html', [
            'token' => $token
        ]);
    }

    /**
     * Zresetuj hasło użytkownika
     * 
     * @return void
     */
    public function resetPasswordAction()
    {
        $token = $_POST['token'];

        $user = $this->getUserOrExit($token);

        if ($user->resetPassword($_POST['password'], $_POST['passwordConfirmation'])) {
            View::renderTemplate('Password/reset_success.html');
        } else {
            View::renderTemplate('Password/reset.html', [
                'token' => $token,
                'user' => $user
            ]);
        }
    }

    /**
     * Znajdź model user powiązany z tokenem resetowania hasła lub
     * zakończ żądanie z wiadomością
     * 
     * @param string $token  Token resetowania hasła wysłany do użytkownika
     * 
     * @return mixed  Obiekt User, jeśli znaleziono i token nie wygasł, w przeciwnym
     * wypadku - null
     */
    private function getUserOrExit($token)
    {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        } else {
            View::renderTemplate('Password/token_expired.html');
            exit;
        }
    }
}
