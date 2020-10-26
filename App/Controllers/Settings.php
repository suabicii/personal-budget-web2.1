<?php

namespace App\Controllers;

use Core\View;

/**
 * Kontroler ustawień
 * 
 * PHP v. 7.4
 */
class Settings extends \Core\Controller
{
    /**
     * Wyświetl stronę z ustawieniami
     * 
     * @return void
     */
    public function indexAction()
    {
        View::renderTemplate("Settings/settings.html");
    }
}
