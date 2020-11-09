<?php

namespace App\Controllers;

use App\Models\Finances;
use Core\View;
use DateTime;
use App\Categories;

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
        // Ustaw domyślny widok i okres po pierwszym wejściu na stronę tuż po logowaniu
        if (!isset($_SESSION['general_view']) && !isset($_SESSION['particular_view'])) {
            $_SESSION['default_view'] = true;
            $_SESSION['general_view'] = true;
            View::renderTemplate('Balance/balance.html');
        } else {
            View::renderTemplate('Balance/balance.html');
        }
    }

    /**
     * Wyświetl bilans w widoku ogólnym
     * 
     * @return void
     */
    public function generalAction()
    {
        if (isset($_SESSION['default_view'])) unset($_SESSION['default_view']);

        $finances = new Finances;

        if (isset($_SESSION['particular_view'])) unset($_SESSION['particular_view']);
        $_SESSION['general_view'] = true;

        $startDateForQuery = static::getDateForQuery($_SESSION['start_date']);
        $endDateForQuery = static::getDateForQuery($_SESSION['end_date']);

        $_SESSION['summed_incomes'] = $finances->getSummedIncomes($startDateForQuery, $endDateForQuery, $_SESSION['user_id']);
        $_SESSION['summed_expenses'] = $finances->getSummedExpenses($startDateForQuery, $endDateForQuery, $_SESSION['user_id']);

        // Tłumaczenie domyślnych kategorii i przypisywanie id do
        // poszczególnych przychodów/wydaktów
        $translatedCategories = [];
        $idsForJS = []; // id do identyfikacji w pliku JS służącym do wyświetlania wykresów
        // i wyliczania różnicy między przychodami a wydatkami (public/js/balance.js)

        foreach ($_SESSION['summed_incomes'] as $income) {
            $translatedCategories[$income['name']] = Categories::translateCategory($income['name']);
            // Zastąp spację myślnikiem
            $idsForJS[$income['name']] = preg_replace('/\s/', '-', $income['name']);
        }

        foreach ($_SESSION['summed_expenses'] as $expense) {
            $translatedCategories[$expense['name']] = Categories::translateCategory($expense['name']);
            $idsForJS[$expense['name']] = preg_replace('/\s/', '-', $expense['name']);
        }


        View::renderTemplate("Balance/tables.html", [
            'translated_categories' => $translatedCategories,
            'idsForJS' => $idsForJS
        ]);
    }

    /**
     * Wyświetl bilans w widoku szczegółowym
     * 
     * @return void
     */
    public function particularAction()
    {
        if (isset($_SESSION['default_view'])) unset($_SESSION['default_view']);

        $finances = new Finances;

        if (isset($_SESSION['general_view'])) unset($_SESSION['general_view']);
        $_SESSION['particular_view'] = true;

        $startDateForQuery = static::getDateForQuery($_SESSION['start_date']);
        $endDateForQuery = static::getDateForQuery($_SESSION['end_date']);

        $_SESSION['all_incomes'] = $finances->getAllIncomes($startDateForQuery, $endDateForQuery, $_SESSION['user_id']);
        $_SESSION['all_expenses'] = $finances->getAllExpenses($startDateForQuery, $endDateForQuery, $_SESSION['user_id']);

        // Tłumaczenie domyślnych kategorii i przypisywanie klas do
        // poszczególnych przychodów/wydaktów
        $translatedCategories = [];
        $classesForJS = [];

        foreach ($_SESSION['all_incomes'] as $income) {
            $translatedCategories[$income['name']] = Categories::translateCategory($income['name']);
            $classesForJS[$income['name']] = preg_replace('/\s/', '-', $income['name']);
        }

        foreach ($_SESSION['all_expenses'] as $expense) {
            $translatedCategories[$expense['expense_category']] = Categories::translateCategory($expense['expense_category']);
            $translatedCategories[$expense['payment_method']] = Categories::translateCategory($expense['payment_method']);
            $classesForJS[$expense['expense_category']] = preg_replace('/\s/', '-', $expense['expense_category']);
        }

        View::renderTemplate("Balance/tables.html", [
            'translated_categories' => $translatedCategories,
            'classesForJS' => $classesForJS
        ]);
    }

    /**
     * Wybór bieżącego miesiąca
     * 
     * @return void
     */
    public function currentMonthAction()
    {
        $today = new DateTime();
        $firstDayOfMonth = new DateTime($today->format('Y-m') . "-01");

        $_SESSION['current_month'] = 'Current month has been selected, my lord';
        static::unsetOtherPeriods($_SESSION['current_month']);

        $_SESSION['start_date'] = $firstDayOfMonth;
        $_SESSION['end_date'] = $today;

        $_SESSION['which_date'] = 'bieżącego miesiąca';

        $_SESSION['recently_chosen_period'] = "current-month";

        $this->redirectToBalance();
    }

    /**
     * Wybór poprzedniego miesiąca
     * 
     * @return void
     */
    public function previousMonthAction()
    {
        $baseDate = new DateTime();
        $baseDate->modify('-1 month');

        $_SESSION['previous_month'] = 'Previous month has been selected, my lord';
        static::unsetOtherPeriods($_SESSION['previous_month']);

        $beginningOfMonth = new DateTime($baseDate->format('Y-m') . '-01');

        $baseDate->modify('last day of this month');

        $endOfMonth = new DateTime($baseDate->format('Y-m-d'));

        $_SESSION['start_date'] = $beginningOfMonth;
        $_SESSION['end_date'] = $endOfMonth;

        $_SESSION['which_date'] = 'poprzedniego miesiąca';

        $_SESSION['recently_chosen_period'] = "previous-month";

        $this->redirectToBalance();
    }

    /**
     * Wybór bieżącego roku
     * 
     * @return void
     */
    public function currentYearAction()
    {
        $beginningOfYear = new DateTime('first day of January');
        $today = new DateTime();

        $_SESSION['current_year'] = 'Current year has been selected, my lord';
        static::unsetOtherPeriods($_SESSION['current_year']);

        $_SESSION['start_date'] = $beginningOfYear;
        $_SESSION['end_date'] = $today;

        $_SESSION['which_date'] = 'bieżącego roku';

        $_SESSION['recently_chosen_period'] = "current-year";

        $this->redirectToBalance();
    }

    /**
     * Wybór niestandardowego okresu
     * 
     * @return void
     */
    public function customDateAction()
    {
        $_SESSION['start_date'] = $_POST['start_date'];
        $_SESSION['end_date'] = $_POST['end_date'];

        $_SESSION['custom_period'] = 'Custom period has been selected, my lord';
        static::unsetOtherPeriods($_SESSION['custom_period']);

        $_SESSION['which_date'] = "okresu od {$_SESSION['start_date']} do {$_SESSION['end_date']}";

        $_SESSION['recently_chosen_period'] = "custom-date";

        $this->redirectToBalance();
    }

    /**
     * Automatyczne ładowanie tabel po odświeżeniu strony
     * 
     * @return void
     */
    public function autoloadAction()
    {
        View::renderTemplate("Balance/tables_autoload.html");
    }

    /**
     * Przekieruj na stronę z bilansem
     * 
     * @return void
     */
    private function redirectToBalance()
    {
        if (isset($_SESSION['general_view'])) $this->redirect('/balance/general');
        else $this->redirect('/balance/particular');
    }

    /**
     * Wyłącz zmienne $_SESSION odpowiadające wyborom poszczególnych okresów
     * 
     * @param string  $period  Zmienna $_SESSION, która odpowiada za aktualnie wybrany okres
     * 
     * @return void
     */
    private static function unsetOtherPeriods($period)
    {
        if (isset($_SESSION['current_month']) && $_SESSION['current_month'] != $period) {
            unset($_SESSION['current_month']);
        } elseif (isset($_SESSION['previous_month']) && $_SESSION['previous_month'] != $period) {
            unset($_SESSION['previous_month']);
        } elseif (isset($_SESSION['current_year']) && $_SESSION['current_year'] != $period) {
            unset($_SESSION['current_year']);
        } elseif (isset($_SESSION['custom_period']) && $_SESSION['custom_period'] != $period) {
            unset($_SESSION['custom_period']);
        }
    }

    /**
     * Przekształć datę początkową/końcową na odpowiedni format do wykonania
     * kwerendy pobierającej przychody/wydatki
     * 
     * @param Object $date  Data początkowa/końcowa
     * 
     * @return mixed  Data po przekształceniu 
     */
    private static function getDateForQuery($date)
    {
        if (isset($_SESSION['custom_period'])) {
            return $date;
        } else {
            return $date->format('Y-m-d');
        }
    }
}
