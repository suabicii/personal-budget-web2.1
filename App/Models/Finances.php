<?php

namespace App\Models;

use PDO;
use Core\View;

class Finances extends \Core\Model
{
    /**
     * Sprawdź poprawność wprowadzonych danych przychodu/wydatku
     * @param float $amount  Kwota
     * @param string $category  Kategoria przychodu/wydatku
     * @param string $date  Data przychodu/wydatku
     * @param string $payment  Sposób płatności (jeśli to wydatek)
     * 
     * @return boolean  True, jeśli wszytkie dane zostały wprowadzone prawidłowo, false w przeciwnym wypadku
     */
    protected function validateIncomeOrExpenseData($amount, $category, $date, $payment = null)
    {
        $today = date('Y-m-d');

        if ($amount < 0) $this->errors[] = 'Podaj liczbę dodatnią';
        if ($date > $today) $this->errors[] = 'Data nie może być późniejsza od dzisiejszej!';
        if ($category == null) $this->errors[] = 'Kategoria nie została wybrana';
        if (isset($_SESSION['adding_expenses'])) {
            if ($payment == null) $this->errors[] = 'Sposób płatności nie został wybrany';
        }

        if (empty($this->errors)) {
            return true;
        } else {
            $_SESSION['errors'] = $this->errors;
            return false;
        }
    }

    /**
     * Dodaj przychód do bazy danych
     * 
     * @param float $amount  Kwota
     * @param string $category  Kategoria przychodu
     * @param string $date  Data otrzymania przychodu
     * @param string $comment  Komentarz (opcjonalnie)
     * 
     * @return boolean  True, jeśli dodanie się powiodło, false w przeciwnym wypadku
     */
    public function addIncomeToDatabase($amount, $category, $date, $comment = '', $user_id)
    {
        if ($this->validateIncomeOrExpenseData($amount, $category, $date)) {
            $db = static::getDB();

            $query = $db->prepare("SELECT id FROM incomes_category_assigned_to_users WHERE user_id = :user_id AND name = :name");
            $query->execute([
                ":user_id" => $user_id,
                ":name" => $category
            ]);
            $category_id = $query->fetch();

            $query = $db->prepare("INSERT INTO incomes VALUES (
                NULL,
                :user_id,
                :category_assigned_to_user_id,
                :amount,
                :date,
                :comment
            )");
            return $query->execute([
                ":user_id" => $user_id,
                ":category_assigned_to_user_id" => $category_id['id'],
                ":amount" => $amount,
                ":date" => $date,
                ":comment" => $comment
            ]);
        }

        return false;
    }

    /**
     * Dodaj wydatek do bazy danych
     * 
     * @param float $amount  Kwota
     * @param string $payment  Sposób płatności
     * @param string $category  Kategoria wydatku
     * @param string $date  Data wydanej kwoty
     * @param string $comment  Komentarz (opcjonalnie)
     * 
     * @return boolean  True, jeśli dodanie się powiodło, false w przeciwnym wypadku
     */
    public function addExpenseToDatabase($amount, $payment, $category, $date, $comment = '', $user_id)
    {
        $_SESSION['adding_expenses'] = true;

        if ($this->validateIncomeOrExpenseData($amount, $category, $date, $payment)) {
            unset($_SESSION['adding_expenses']);

            $db = static::getDB();

            $query = $db->prepare("SELECT id FROM expenses_category_assigned_to_users WHERE user_id = :user_id AND name = :name");
            $query->execute([
                ":user_id" => $user_id,
                ":name" => $category
            ]);
            $category_id = $query->fetch();

            $query = $db->prepare("SELECT id FROM payment_methods_assigned_to_users WHERE user_id = :user_id AND name = :name");
            $query->execute([
                ":user_id" => $user_id,
                ":name" => $payment
            ]);
            $payment_id = $query->fetch();

            $query = $db->prepare("INSERT INTO expenses VALUES (
                NULL,
                :user_id,
                :category_assigned_to_user_id,
                :payment_assigned_to_user_id,
                :amount,
                :date,
                :comment
            )");
            return $query->execute([
                ":user_id" => $user_id,
                ":category_assigned_to_user_id" => $category_id['id'],
                ":payment_assigned_to_user_id" => $payment_id['id'],
                ":amount" => $amount,
                ":date" => $date,
                ":comment" => $comment
            ]);
        }

        unset($_SESSION['adding_expenses']);

        return false;
    }

    /**
     * Pobierz zsumowane przychody
     * 
     * @param string $startDate  Data początku okresu
     * @param string $endDate  Data końca okresu
     * 
     * @return array  Tablica asocjacyjna ze zsumowanymi przychodami
     */
    public function getSummedIncomes($startDate, $endDate, $user_id)
    {
        $db = static::getDB();

        $income_category = 'income_category_assigned_to_user_id';

        $query = $db->prepare("
            SELECT {$income_category}, name, SUM(amount) as amount FROM incomes, incomes_category_assigned_to_users 
            WHERE incomes.user_id = {$user_id} AND incomes_category_assigned_to_users.id = incomes.{$income_category}
            AND date_of_income BETWEEN '{$startDate}' AND '{$endDate}' 
            GROUP BY {$income_category} ORDER BY amount DESC
        ");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Pobierz zsumowane wydatki
     * 
     * @param string $startDate  Data początku okresu
     * @param string $endDate  Data końca okresu
     * 
     * @return array  Tablica asocjacyjna ze zsumowanymi wydatkami
     */
    public function getSummedExpenses($startDate, $endDate, $user_id)
    {
        $db = static::getDB();

        $expense_category = 'expense_category_assigned_to_user_id';

        $query = $db->prepare("SELECT {$expense_category}, name, SUM(amount) as amount FROM expenses, expenses_category_assigned_to_users WHERE expenses.user_id = {$user_id} AND expenses_category_assigned_to_users.id = expenses.{$expense_category} AND date_of_expense BETWEEN '{$startDate}' AND '{$endDate}' GROUP BY {$expense_category} ORDER BY amount DESC");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Pobierz WSZYSTKIE przychody
     * 
     * @param string $startDate  Data początku okresu
     * @param string $endDate  Data końca okresu
     * 
     * @return array  Tablica asocjacyjna ze wszystkimi przychodami
     */
    public function getAllIncomes($startDate, $endDate, $user_id)
    {
        $db = static::getDB();

        $income_category = 'income_category_assigned_to_user_id';

        $query =  $db->prepare("SELECT incomes.user_id, income_category_assigned_to_user_id, name, amount, date_of_income, income_comment FROM incomes, incomes_category_assigned_to_users WHERE incomes_category_assigned_to_users.id = incomes.{$income_category} AND incomes.user_id = {$user_id} AND date_of_income BETWEEN '{$startDate}' AND '{$endDate}' ORDER BY amount DESC");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Pobierz WSZYSTKIE wydatki
     * 
     * @param string $startDate  Data początku okresu
     * @param string $endDate  Data końca okresu
     * 
     * @return array  Tablica asocjacyjna ze wszystkimi wydatkami
     */
    public function getAllExpenses($startDate, $endDate, $user_id)
    {
        $db = static::getDB();

        $expense_category = 'expense_category_assigned_to_user_id';
        $payment_method = 'payment_method_assigned_to_user_id';

        $query =  $db->prepare("SELECT expenses.user_id, {$expense_category}, expenses_category_assigned_to_users.name AS expense_category, payment_methods_assigned_to_users.name AS payment_method, amount, date_of_expense, expense_comment FROM expenses, expenses_category_assigned_to_users, payment_methods_assigned_to_users WHERE expenses_category_assigned_to_users.id = expenses.{$expense_category} AND payment_methods_assigned_to_users.id = expenses.{$payment_method} AND expenses.user_id = {$user_id} AND date_of_expense BETWEEN '{$startDate}' AND '{$endDate}' ORDER BY amount DESC");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Pobierz kategorie przychodów powiązane z danym użytkownikiem
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * 
     * @return array  Tablica asocjacyjna z kategoriami
     */
    public function getIncomesCategories($user_id)
    {
        $db = static::getDB();

        $query = $db->prepare("SELECT name FROM incomes_category_assigned_to_users WHERE user_id = {$user_id}");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Pobierz kategorie wydatków powiązane z danym użytkownikiem
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * 
     * @return array  Tablica asocjacyjna z kategoriami
     */
    public function getExpensesCategories($user_id)
    {
        $db = static::getDB();

        $query = $db->prepare("SELECT name FROM expenses_category_assigned_to_users WHERE user_id = {$user_id}");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Pobierz sposoby płatności powiązane z danym użytkownikiem
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * 
     * @return array  Tablica asocjacyjna z metodami płatności
     */
    public function getPaymentMethods($user_id)
    {
        $db = static::getDB();

        $query = $db->prepare("SELECT name FROM payment_methods_assigned_to_users WHERE user_id = {$user_id}");
        $query->execute();

        return $query->fetchAll();
    }
}
