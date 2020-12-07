<?php

namespace App\Controllers;

use Core\View;
use App\Models\Finances;
use App\Flash;
use App\Categories;
use DateTime;

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

        foreach ($expenseCategories as $category) {
            $translatedCategories[$category['name']] = Categories::translateCategory($category['name']);
        }

        foreach ($paymentMethods as $method) {
            $translatedCategories[$method['name']] = Categories::translateCategory($method['name']);
        }

        View::renderTemplate('Expense/add-expense.html', [
            'expense_categories' => $expenseCategories,
            'translated_categories' => $translatedCategories,
            'payment_methods' => $paymentMethods,
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

    /**
     * Pobierz, jeśli istnieje, kwotę limitu w danej kategorii
     * 
     * @return void
     */
    public function getLimitAction()
    {
        $expense = new Finances;

        $limit = $expense->getExpenseLimit($_SESSION['user_id'], $_GET['category']);

        if ($limit != null) {
            $expenseSum = $this->getExpenseSumInCategory($_GET['category']);

            if ($expenseSum == null) $expenseSum = 0;

            echo <<<END
            <div id='limitation' class='d-flex justify-content-between'>
                <small>Limit: <strong class='text-danger' id='amount-limit-fetched'>{$limit}</strong></small>
                <small>Dotychczas wydano: <strong class='text-danger' id='expense-sum'>{$expenseSum}</strong></small>
            </div>
END;
        } else {
            echo "";
        }
    }

    /**
     * Pobierz sumę wydatków w danej kategorii z bieżącego miesiąca
     * 
     * @param string $category  Kategoria wydatku
     * 
     * @return mixed  Ilość pieniędzy, które do tej pory wydał użytkownik w danej kategorii
     * lub null
     */
    private function getExpenseSumInCategory($category)
    {
        $expense = new Finances;

        $today = new DateTime();
        $firstDayOfMonth = new DateTime($today->format('Y-m') . "-01");

        $summedExpenses = $expense->getSummedExpenses($firstDayOfMonth->format("Y-m-d"), $today->format("Y-m-d"), $_SESSION['user_id']);

        foreach ($summedExpenses as $summedExpense) {
            if ($summedExpense['name'] == $category) {
                return $summedExpense['amount'];
            }
        }
    }
}
