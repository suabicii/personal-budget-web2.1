<?php

namespace App\Controllers;

use Core\View;
use App\Models\Finances;
use App\Flash;
use App\Categories;

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

        $income = new Finances;

        $incomeCategories = $income->getIncomesCategories($_SESSION['user_id']);
        $translatedCategories = [];
        $optionValues = [];

        foreach ($incomeCategories as $category) {
            $translatedCategories[$category['name']] = Categories::translateCategory($category['name']);
        }

        View::renderTemplate('Income/add-income.html', [
            'income_categories' => $incomeCategories,
            'translated_categories' => $translatedCategories,
        ]);
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

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        } else {
            Flash::addMessage('Nie udało się dodać przychodu', Flash::WARNING);

            $messages = Flash::getMessages();

            foreach ($messages as $message) {
                echo "<div class='alert alert-{$message['type']}'>{$message['body']}</div>";
            }
        }
    }
}
