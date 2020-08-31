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
            $_SESSION['user_id'] = $user->id;
            $_SESSION['logged_name'] = $user->name;
            $this->redirect('/home');
        } else {
            View::renderTemplate('Start/index.html', [
                'user' => $user
            ]);
        }
    }

    /**
     * Po zalogowaniu
     * 
     * @return void
     */
    public function successAction()
    {
        View::renderTemplate('Home/index.html');
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
