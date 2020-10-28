<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Models\Finances;
use App\Flash;

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
    public function getIncomesCategoriesAction()
    {
        $finances = new Finances;

        $_SESSION['incomes_categories'] = $finances->getIncomesCategories($_SESSION['user_id']);

        View::renderTemplate("Settings/incomes-categories.html");
    }

    /**
     * Pobierz kategorie wydatków
     * 
     * @return void
     */
    public function getExpensesCategoriesAction()
    {
        $finances = new Finances;

        $_SESSION['expenses_categories'] = $finances->getExpensesCategories($_SESSION['user_id']);

        View::renderTemplate("Settings/expenses-categories.html");
    }

    /**
     * Pobierz sposoby płatności
     * 
     * @return void
     */
    public function getPaymentMethodsAction()
    {
        $finances = new Finances;

        $_SESSION['payment_methods'] = $finances->getPaymentMethods($_SESSION['user_id']);

        View::renderTemplate("Settings/payment-methods.html");
    }

    /**
     * Zmień dane użytkownika
     * 
     * @return void
     */
    public function changeUserDataAction()
    {
        $user = User::findByID($_SESSION['user_id']);

        if ($user->changeUserData($_POST['username'], $_POST['email'], $_POST['name'], $_POST['old_password'], $_POST['new_password'], $_POST['new_password_confirmation'])) {
            Flash::addMessage('Dane zostały zmienione');

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        } else {
            Flash::addMessage('Nie udało się zmienić danych', Flash::WARNING);

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }

            $errors = $user->errors;

            echo '<div class="errors">';
            echo '<p class="text-danger text-center mb-2">Lista błędów:</p>';
            echo '<ul>';
            foreach ($errors as $error) {
                echo "<li>{$error}</li>";
            }
            echo '</ul>';
            echo '</div>';
        }
    }
}
