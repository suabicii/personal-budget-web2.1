<?php

namespace App\Controllers;

use Core\View;
use App\Models\Finances;
use App\Flash;
use App\Categories;

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

        $expense = new Finances;

        $expenseCategories = $expense->getexpensesCategories($_SESSION['user_id']);
        $paymentMethods = $expense->getPaymentMethods($_SESSION['user_id']);
        $translatedCategories = [];
        $optionValues = [];

        foreach ($expenseCategories as $category) {
            $translatedCategories[$category['name']] = Categories::translateCategory($category['name']);
            $optionValues[$category['name']] = preg_replace('/\s/', '-', $category['name']);
        }

        foreach ($paymentMethods as $method) {
            $translatedCategories[$method['name']] = Categories::translateCategory($method['name']);
            $optionValues[$method['name']] = preg_replace('/\s/', '-', $method['name']);
        }

        View::renderTemplate('Expense/add-expense.html', [
            'expense_categories' => $expenseCategories,
            'translated_categories' => $translatedCategories,
            'payment_methods' => $paymentMethods,
            'option_values' => $optionValues
        ]);
    }

    /**
     * Dodaj wydatek
     * 
     * @return void
     */
    public function AddAction()
    {
        $expense = new Finances;

        if ($expense->addExpenseToDatabase($_POST['amount'], $_POST['payment'], $_POST['category'], $_POST['date'], $_POST['comment'], $_SESSION['user_id'])) {
            Flash::addMessage('Wydatek został dodany');

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        } else {
            Flash::addMessage('Nie udało się dodać wydatku', Flash::WARNING);

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        }
    }
}
