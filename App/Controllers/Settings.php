<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Models\Finances;
use App\Flash;
use App\Categories;

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

        $tranlsated_categories = [];

        foreach ($_SESSION['incomes_categories'] as $category) {
            $tranlsated_categories[$category['name']] = Categories::translateCategory($category['name']);
        }

        View::renderTemplate("Settings/incomes-categories.html", [
            'translated_categories' => $tranlsated_categories
        ]);
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

        foreach ($_SESSION['expenses_categories'] as $category) {
            $tranlsated_categories[$category['name']] = Categories::translateCategory($category['name']);
        }

        View::renderTemplate("Settings/expenses-categories.html", [
            'translated_categories' => $tranlsated_categories
        ]);
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

        foreach ($_SESSION['payment_methods'] as $method) {
            $tranlsated_categories[$method['name']] = Categories::translateCategory($method['name']);
        }


        View::renderTemplate("Settings/payment-methods.html", [
            'translated_categories' => $tranlsated_categories
        ]);
    }

    /** EDYCJA KATEGORII/SPOSOBÓW PRZYCHODÓW/WYDATKÓW/PŁATNOŚCI */

    /**
     * Edytuj kategorię przychodu/wydatku/sposób płatności
     * 
     * @return void
     */
    public function editAction()
    {
        $finances = new Finances;

        if ($finances->editCategory($_SESSION['user_id'], $_POST['old_name'], $_POST['new_name'], $_POST['table_name'])) {
            Flash::addMessage('Kategoria została zmieniona');

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        } else {
            Flash::addMessage('Nie udało się zmienić kategorii', Flash::WARNING);

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        }
    }

    /** USUWANIE KATEGORII/SPOSOBÓW PŁATNOŚCI */

    /**
     * Usuń kategorię przychodu/wydatku/sposób płatności
     * 
     * @return void
     */
    public function deleteAction()
    {
        $finances = new Finances;

        if ($finances->deleteCategory($_SESSION['user_id'], $_POST['category'], $_POST['table_name'])) {
            Flash::addMessage('Pomyślnie usunięto kategorię');

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        } else {
            Flash::addMessage('Nie udało się usunąć kategorii', Flash::WARNING);

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        }
    }

    /** EDYCJA DANYCH UŻYTKOWNIKA */

    /**
     * Zmień dane użytkownika
     * 
     * @return void
     */
    public function changeUserDataAction()
    {
        $user = User::findByID($_SESSION['user_id']);

        if ($user->saveNewDataTemporarily($_POST['username'], $_POST['email'], $_POST['name'], $_POST['old_password'], $_POST['new_password'], $_POST['new_password_confirmation'])) {
            Flash::addMessage('Nowe dane zostały zapisane. Aby dokończyć edycję, sprawdź pocztę i kliknij na link umieszczony w mailu');

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

            echo '<div class="errors">';
            foreach ($user->errors as $error) {
                echo "<p class='text-danger'>{$error}</p>";
            }
            echo '</div>';
        }
    }

    /**
     * Wyświetl stronę do potwierdzenia zmiany danych
     * 
     * @return void
     */
    public function confirmAction()
    {
        $token = $this->route_params['token'];

        View::renderTemplate("Settings/confirm.html", [
            'token' => $token
        ]);
    }

    /**
     * Potwierdź edycję danych
     * 
     * @return void
     */
    public function confirmEditAction()
    {
        $token = $_POST['token'];

        $user = $this->getUserOrExit($token);

        if ($user->changeUserData($token)) {
            View::renderTemplate('Settings/edit_success.html');
        } else {
            View::renderTemplate('Settings/edit_fail.html');
        }
    }

    /**
     * Znajdź model user powiązany z tokenem resetowania hasła lub
     * zakończ żądanie z wiadomością
     * 
     * @param string $token  Token resetowania hasła wysłany do użytkownika
     * 
     * @return mixed  Obiekt User, jeśli znaleziono i token nie wygasł, w przeciwnym
     * wypadku - null
     */
    private function getUserOrExit($token)
    {
        $user = User::findByEditData($token);

        if ($user) {
            return $user;
        } else {
            View::renderTemplate('Settings/token_expired.html');
            exit;
        }
    }
}
