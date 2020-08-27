<?php

namespace App\Controllers;

use App\Models\User;
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
            # Miejsce na funkcję wysyłania maila aktywacyjnego
            $this->redirect('/');
        } else {
            View::renderTemplate('Start/index.html', [
                'user' => $user
            ]);
        }
    }
}
