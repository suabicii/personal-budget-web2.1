<?php

namespace App\Controllers;

use \Core\View;

/**
 * Kontroler strony głównej - widocznej po zalogowaniu
 * 
 * PHP v 7+
 */
class Home extends Authenticated
{
    /**
     * Wyświetl stronę główną
     * 
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Home/index.html');
    }
}
