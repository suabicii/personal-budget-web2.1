<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
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
        View::renderTemplate('Income/add-income.html');
    }

    /**
     * Dodaj przychód
     * 
     * @return void
     */
    public function AddAction()
    {
        $user = User::findByID($_SESSION['user_id']);

        if ($user->addIncomeToDatabase($_POST['amount'], $_POST['category'], $_POST['date'], $_POST['comment'])) {
            Flash::addMessage('Przychód został dodany');
            $this->redirect('/home');
        } else {
            Flash::addMessage('Nie udało się dodać przychodu', Flash::WARNING);
            $this->redirect('/add-income');
        }
    }
}
