<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Models\Finances;

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
        View::renderTemplate("Settings/settings.html", [
            'user' => User::findByID($_SESSION['user_id'])
        ]);
    }

    /**
     * Pobierz kategorie przychodów
     * 
     * @return void
     */
    public function getIncomesCategories()
    {
        $incomes = new Finances;

        $_SESSION['incomes_categories'] = $incomes->getIncomesCategories($_SESSION['user_id']);

        View::renderTemplate("Settings/incomes-categories.html");
    }

    /**
     * Pobierz kategorie wydatków
     * 
     * @return void
     */
    public function getExpensesCategories()
    {
        $incomes = new Finances;

        $_SESSION['expenses_categories'] = $incomes->getExpensesCategories($_SESSION['user_id']);

        View::renderTemplate("Settings/expenses-categories.html");
    }

    /**
     * Pobierz sposoby płatności
     * 
     * @return void
     */
    public function getPaymentMethods()
    {
        $incomes = new Finances;

        $_SESSION['payment_methods'] = $incomes->getPaymentMethods($_SESSION['user_id']);

        View::renderTemplate("Settings/payment-methods.html");
    }
}
