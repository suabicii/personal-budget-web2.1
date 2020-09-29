<?php

namespace App\Controllers;

use App\Models\User;
use App\Auth;
use App\Flash;
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
        $remember_me = isset($_POST['remember_me']);

        if ($user) {
            Auth::login($user, $remember_me);
            $this->redirect('/home');
        } else {
            View::renderTemplate('Start/index.html', [
                'user' => $user,
                'remember_me' => $remember_me
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

    /**
     * Wyślij ponownie link aktywacyjny
     * 
     * @return void
     */
    public function resendActivationLinkAction()
    {
        $user = User::findByEmail($_POST['email']);

        if ($user) {
            $_SESSION['resenend_activation_email'] = true;
            $user->sendActivationEmail();
            Flash::addMessage('Sprawdź swoją skrzynkę odbiorczą na koncie pocztowym', Flash::INFO);
            View::renderTemplate('Mail/activate.html');
        } else {
            Flash::addMessage('Nie ma użytkownika o podanym adresie e-mail', Flash::WARNING);
            $this->redirect('/');
        }
    }
}
