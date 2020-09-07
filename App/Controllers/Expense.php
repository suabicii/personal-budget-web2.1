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
        View::renderTemplate('Expense/add-expense.html');
    }
}
