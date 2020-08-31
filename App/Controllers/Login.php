<?php

namespace App\Controllers;

use App\Models\User;
use App\Auth;
use Core\View;

class Login extends \Core\Controller
{
    /**
     * Logowanie
     * 
     * @return void
     */
    public function createAction()
    {
        $user = User::authenticate($_POST['userLogin'], $_POST['authorization']);

        if ($user) {
            Auth::login($user, $_POST['remember_me']);
            $this->redirect('/home');
        } else {
            View::renderTemplate('Start/index.html', [
                'user' => $user
            ]);
        }
    }

    /**
     * Wyloguj użytkownika
     * 
     * @return void
     */
    public function destroyAction()
    {
        Auth::logout();
        $this->redirect('/');
    }
}
