<?php

namespace App\Controllers;

use \Core\View;

/**
 * Kontroler strony startowej - strony wyświetlającej się na początku, przed logowaniem
 * 
 * PHP v. 7+
 */
class Start extends \Core\Controller
{
    /**
     * Wyświetl stronę index.html
     * 
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Start/index.html');
    }
}
