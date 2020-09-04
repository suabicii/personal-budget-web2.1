<?php

namespace App\Controllers;

use Core\View;

/**
 * Kontroler do dodawania przychodów
 * 
 * PHP v. 7.4
 */
class Income extends \Core\Controller
{
    /**
     * Wyświetl stronę do dodawania przychodów
     * 
     * @return void
     */
    public function IndexAction()
    {
        View::renderTemplate('Income/add-income.html');
    }

    /**
     * Dodaj przychód
     * 
     * @param int $user_id  Id użytkownika
     * @param int $amount  Kwota
     * @param string $category  Kategoria przychodu
     * @param string $date  Data otrzymania przychodu
     * @param string $comment  Komentarz (opcjonalnie)
     * 
     * @return void
     */
    public function AddAction()
    {
    }
}
