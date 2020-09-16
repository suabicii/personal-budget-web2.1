<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Flash;

/**
 * Kontroler do dodawania wydatków
 * 
 * PHP v. 7.4
 */
class Expense extends \Core\Controller
{
    /**
     * Wyświetl stronę do dodawania wydatków
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

        View::renderTemplate('Expense/add-expense.html');
    }

    /**
     * Dodaj wydatek
     * 
     * @return void
     */
    public function AddAction()
    {
        $user = User::findByID($_SESSION['user_id']);

        if ($user->addExpenseToDatabase($_POST['amount'], $_POST['payment'], $_POST['category'], $_POST['date'], $_POST['comment'])) {
            Flash::addMessage('Wydatek został dodany');
            $this->redirect('/home');
        } else {
            Flash::addMessage('Nie udało się dodać wydatku', Flash::WARNING);
            $this->redirect('/add-expense');
        }
    }
}
