<?php

namespace App\Controllers;

use Core\View;
use App\Models\Finances;
use App\Flash;

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
        // Jeśli ostatnio przeglądaną stroną jest strona z bilansem
        if (isset($_SESSION['general_view'])) {
            unset($_SESSION['general_view']);
        } elseif (isset($_SESSION['particular_view'])) {
            unset($_SESSION['particular_view']);
        }

        View::renderTemplate('Income/add-income.html');
    }

    /**
     * Dodaj przychód
     * 
     * @return void
     */
    public function AddAction()
    {
        $income = new Finances;

        if ($income->addIncomeToDatabase($_POST['amount'], $_POST['category'], $_POST['date'], $_POST['comment'], $_SESSION['user_id'])) {
            Flash::addMessage('Przychód został dodany');
            $this->redirect('/add-income');
        } else {
            Flash::addMessage('Nie udało się dodać przychodu', Flash::WARNING);
            $this->redirect('/add-income');
        }
    }
}
