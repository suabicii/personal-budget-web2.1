<?php

namespace App\Controllers;

use \Core\View;
use App\Auth;

/**
 * Kontroler strony startowej - strony wyświetlającej się na początku, przed logowaniem
 * 
 * PHP v. 7+
 */
class Start extends \Core\Controller
{
    /**
     * Wyświetl stronę startową (przed logowaniem)
     * 
     * @return void
     */
    public function indexAction()
    {
        if (Auth::getUser()) $this->redirect('/home');
        else View::renderTemplate('Start/index.html');
    }
}
