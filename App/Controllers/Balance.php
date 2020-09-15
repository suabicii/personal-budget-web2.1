<?php

namespace App\Controllers;

use App\Models\User;
use Core\View;
use DateTime;

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
            $_SESSION['general_view'] = true;
            $this->redirect('/balance/current-month');
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
        $user = User::findByID($_SESSION['user_id']);

        if (isset($_SESSION['particular_view'])) unset($_SESSION['particular_view']);
        $_SESSION['general_view'] = true;

        $startDate = $_SESSION['start_date'];
        $endDate = $_SESSION['end_date'];

        if (isset($_SESSION['custom_period'])) {
            $startDateForQuery = $startDate;
            $endDateForQuery = $endDate;
        } else {
            $startDateForQuery = $startDate->format('Y-m-d');
            $endDateForQuery = $endDate->format('Y-m-d');
        }

        $_SESSION['summed_incomes'] = $user->getSummedIncomes($startDateForQuery, $endDateForQuery);
        $_SESSION['summed_expenses'] = $user->getSummedExpenses($startDateForQuery, $endDateForQuery);

        $this->redirect('/balance');
    }

    /**
     * Wyświetl bilans w widoku szczegółowym
     * 
     * @return void
     */
    public function particularAction()
    {
        $user = User::findByID($_SESSION['user_id']);

        if (isset($_SESSION['general_view'])) unset($_SESSION['general_view']);
        $_SESSION['particular_view'] = true;

        $startDate = $_SESSION['start_date'];
        $endDate = $_SESSION['end_date'];

        if (isset($_SESSION['custom_period'])) {
            $startDateForQuery = $startDate;
            $endDateForQuery = $endDate;
        } else {
            $startDateForQuery = $startDate->format('Y-m-d');
            $endDateForQuery = $endDate->format('Y-m-d');
        }

        $_SESSION['all_incomes'] = $user->getAllIncomes($startDateForQuery, $endDateForQuery);
        $_SESSION['all_expenses'] = $user->getAllExpenses($startDateForQuery, $endDateForQuery);

        $this->redirect('/balance');
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

        $_SESSION['current_month'] = 'Current month has been selected, my master';
        static::unsetOtherPeriods($_SESSION['current_month']);

        $_SESSION['start_date'] = $firstDayOfMonth;
        $_SESSION['end_date'] = $today;

        $_SESSION['which_date'] = 'bieżącego miesiąca';

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

        $_SESSION['previous_month'] = 'Previous month has been selected, my master';
        static::unsetOtherPeriods($_SESSION['previous_month']);

        $beginningOfMonth = new DateTime($baseDate->format('Y-m') . '-01');

        $baseDate->modify('last day of this month');

        $endOfMonth = new DateTime($baseDate->format('Y-m-d'));

        $_SESSION['start_date'] = $beginningOfMonth;
        $_SESSION['end_date'] = $endOfMonth;

        $_SESSION['which_date'] = 'poprzedniego miesiąca';

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

        $_SESSION['current_year'] = 'Current year has been selected, my master';
        static::unsetOtherPeriods($_SESSION['current_year']);

        $_SESSION['start_date'] = $beginningOfYear;
        $_SESSION['end_date'] = $today;

        $_SESSION['which_date'] = 'bieżącego roku';

        $this->redirectToBalance();
    }

    /**
     * Wybór niestandardowego okresu
     * 
     * @return void
     */
    public function customDateAction()
    {
        $_SESSION['start_date'] = $_POST['start-date'];
        $_SESSION['end_date'] = $_POST['end-date'];

        $_SESSION['custom_period'] = 'Custom period has been selected, my master';
        static::unsetOtherPeriods($_SESSION['custom_period']);

        $_SESSION['which_date'] = "okresu od {$_SESSION['start_date']} do {$_SESSION['end_date']}";

        $this->redirectToBalance();
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
}
