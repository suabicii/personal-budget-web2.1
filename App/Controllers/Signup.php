<?php

namespace App\Controllers;

use App\Models\User;
use App\Flash;
use Core\View;

class Signup extends \Core\Controller
{
    /**
     * Rejestracja użytkownika
     * 
     * @return void
     */
    public function createAction()
    {
        $user = new User($_POST);

        if ($user->save()) {
            $user->sendActivationEmail();
            Flash::addMessage('Sprawdź swoją skrzynkę odbiorczą na koncie pocztowym', Flash::INFO);
            View::renderTemplate('Mail/activate.html');
        } else {
            View::renderTemplate('Start/index.html', [
                'user' => $user
            ]);
        }
    }

    /**
     * Informacja o wysłaniu linka aktywacyjnego
     * 
     * @return void
     */
    public function activateAction()
    {
        View::renderTemplate('Mail/activate.html');
    }
}
