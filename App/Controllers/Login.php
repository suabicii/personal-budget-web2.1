<?php

namespace App\Controllers;

use App\Models\User;
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
            $this->redirect('/');
        } else {
            View::renderTemplate('Start/index.html', [
                'user' => $user
            ]);
        }
    }
}
