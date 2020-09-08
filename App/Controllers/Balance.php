<?php

namespace App\Controllers;

use Core\View;

/**
 * Kontroler do wyliczania bilansu
 * 
 * PHP v. 7.4
 */
class Balance extends \Core\Controller
{
    /**
     * Wyświetl stronę z bilansem
     * 
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate('Balance/balance.html');
    }
}
