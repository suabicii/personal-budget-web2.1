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
     * Aktywacja konta
     * 
     * @return void
     */
    public function activateAction()
    {
        User::activate($this->route_params['token']);
        $this->redirect('/signup/activated');
    }

    /**
     * Wyświetl stronę z informacją o udanej aktywacji konta
     * 
     * @return void
     */
    public function activatedAction()
    {
        View::renderTemplate('Signup/activated.html');
    }
}
