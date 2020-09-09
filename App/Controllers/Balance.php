<?php

namespace App\Controllers;

use App\Models\User;
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

    /**
     * Wyświetl bilans w widoku ogólnym
     * 
     * @return void
     */
    public function generalAction()
    {
        $user = User::findByID($_SESSION['user_id']);

        $_SESSION['summed_incomes'] = $user->getSummedIncomes('2020-09-01', date('Y-m-d'));
        $_SESSION['summed_expenses'] = $user->getSummedExpenses('2020-09-01', date('Y-m-d'));

        $this->redirect('/balance');
    }
}
