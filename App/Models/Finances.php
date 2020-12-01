<?php

namespace App\Models;

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
            WHERE incomes.user_id = {$user_id} 
                AND incomes_category_assigned_to_users.id = incomes.{$income_category}
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

        $query = $db->prepare("SELECT {$expense_category}, name, SUM(amount) as amount FROM expenses, expenses_category_assigned_to_users 
        WHERE expenses.user_id = {$user_id} 
            AND expenses_category_assigned_to_users.id = expenses.{$expense_category} 
            AND date_of_expense BETWEEN '{$startDate}' AND '{$endDate}' 
        GROUP BY {$expense_category} ORDER BY amount DESC");
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

        $query =  $db->prepare("SELECT incomes.id, incomes.user_id, income_category_assigned_to_user_id, name, amount, date_of_income, income_comment FROM incomes, incomes_category_assigned_to_users WHERE incomes_category_assigned_to_users.id = incomes.{$income_category} 
            AND incomes.user_id = {$user_id} 
            AND date_of_income BETWEEN '{$startDate}' AND '{$endDate}' 
        ORDER BY amount DESC");
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

        $this->checkPaymentMethodsDeletion($user_id);

        $query =  $db->prepare("SELECT expenses.id, expenses.user_id, {$expense_category}, expenses_category_assigned_to_users.name AS expense_category, payment_methods_assigned_to_users.name AS payment_method, amount, date_of_expense, expense_comment FROM expenses, expenses_category_assigned_to_users, payment_methods_assigned_to_users
        WHERE expenses_category_assigned_to_users.id = expenses.{$expense_category} 
            AND payment_methods_assigned_to_users.id = expenses.{$payment_method}
            AND expenses.user_id = {$user_id} 
            AND date_of_expense BETWEEN '{$startDate}' AND '{$endDate}'
        ORDER BY amount DESC");
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Sprawdź, czy isnieją w bazie danych sposoby płatności z konkretnymi Id.
     * Jeśli ich nie ma, wstaw rekordy o wartościach "Brak danych"
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * 
     * @return void
     */
    private function checkPaymentMethodsDeletion($user_id)
    {
        $db = static::getDB();

        $payment_method = 'payment_method_assigned_to_user_id';

        $query = $db->prepare("SELECT expenses.{$payment_method} AS payment_id FROM expenses 
        WHERE user_id = {$user_id} GROUP BY {$payment_method}");
        $query->execute();

        $paymentIds = $query->fetchAll();

        foreach ($paymentIds as $paymentId) {
            $query = $db->prepare("SELECT id FROM payment_methods_assigned_to_users
            WHERE id = {$paymentId['payment_id']}");
            $query->execute();

            $existingId = $query->fetch();

            if (empty($existingId)) {
                $query = $db->prepare("INSERT INTO payment_methods_assigned_to_users 
                VALUES ({$paymentId['payment_id']}, {$user_id}, 'Brak danych')");
                $query->execute();
            }
        }
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

        $query = $db->prepare("SELECT name FROM incomes_category_assigned_to_users 
            WHERE user_id = {$user_id}");
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

        $query = $db->prepare("SELECT name FROM expenses_category_assigned_to_users 
            WHERE user_id = {$user_id}");
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

        $query = $db->prepare("SELECT name FROM payment_methods_assigned_to_users 
            WHERE user_id = {$user_id}");
        $query->execute();

        return $query->fetchAll();
    }

    /** EDYCJA I USUWANIE */

    /**
     * Edytuj kategorię przychodu/wydatku/metodę płatności
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * @param string $oldName  Aktualna nazwa kategorii
     * @param string $newName  Nowa nazwa kategorii
     * @param string $tableName  Nazwa tabeli z nazwami kategorii
     * 
     * @return boolean  True, jeśli edycja się powiodła, false w przeciwnym wypadku
     */
    public function editCategory($user_id, $oldName, $newName, $tableName)
    {
        $db = static::getDB();

        $query = $db->prepare("UPDATE {$tableName} SET name = '{$newName}'
            WHERE name = '{$oldName}' AND user_id = {$user_id}
        ");

        return $query->execute();
    }

    /**
     * Usuń kategorię przychodu/wydatku/sposób płatności
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * @param string $category  Nazwa kategorii
     * @param string $tableName  Nazwa tabeli, w której znajduje się dana kategoria
     * 
     * @return boolean  True, jeśli pomyślnie usunięto kategorię, false w przeciwnym
     * wypadku
     */
    public function deleteCategory($user_id, $category, $tableName)
    {
        $db = static::getDB();

        $query = $db->prepare("DELETE FROM {$tableName} 
            WHERE name = '{$category}' AND user_id = {$user_id}
        ");

        return $query->execute();
    }

    /** DODAWANIE KATEGORII */

    /**
     * Dodaj kategorię przychodu/wydatku lub sposób płatności
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * @param string $category  Nazwa kategorii
     * @param string $tableName  Nazwa tabeli, w której ma znaleźć się nowa kategoria
     * 
     * @return boolean  True, jeśli pomyślnie dodano kategorię, false w przeciwnym
     * wypadku
     */
    public function addCategory($user_id, $category, $tableName)
    {
        $db = static::getDB();

        $query = $db->prepare("INSERT INTO {$tableName} VALUES (NULL, {$user_id}, '{$category}')");

        return $query->execute();
    }

    /** EDYCJA POJEDNYCZYCH PRYCHODÓW/WYDATKÓW */

    /**
     * Edytuj pojedynczy przychód
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * @param int $income_id  Id danego przychodu
     * @param string $category  Kategoria przychodu
     * @param string $date  Data otrzymania przychodu
     * @param float $amount  Kwota
     * @param string $comment  Komentarz
     * 
     * @return boolean  True, jeśli edycja się powiodła, false w przeciwnym wypadku
     */
    public function editSingleIncome($user_id, $income_id, $category, $date, $amount, $comment = "")
    {
        $db = static::getDB();

        // Znajdź id kategorii powiązanej z zalogowanym użytkownikiem
        $query = $db->prepare("SELECT id FROM incomes_category_assigned_to_users
            WHERE user_id = {$user_id} AND name = '{$category}'
        ");
        $query->execute();

        $categoryId = $query->fetch();

        $query = $db->prepare("UPDATE incomes SET 
            income_category_assigned_to_user_id = {$categoryId['id']},
            amount = {$amount},
            date_of_income = '{$date}',
            income_comment = '{$comment}'
            WHERE id = {$income_id}
        ");

        return $query->execute();
    }

    /**
     * Edytuj pojedynczy wydatek
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * @param int $income_id  Id danego przychodu
     * @param string $category  Kategoria przychodu
     * @param string $payment_method  Sposób płatności
     * @param string $date  Data wydania pieniędzy
     * @param float $amount  Kwota
     * @param string $comment  Komentarz
     * 
     * @return boolean  True, jeśli edycja się powiodła, false w przeciwnym wypadku
     */
    public function editSingleExpense($user_id, $expense_id, $category, $payment_method, $date, $amount, $comment = "")
    {
        $db = static::getDB();

        // Znajdź id kategorii powiązanej z zalogowanym użytkownikiem
        $query = $db->prepare("SELECT id FROM expenses_category_assigned_to_users
            WHERE user_id = {$user_id} AND name = '{$category}'
        ");
        $query->execute();

        $categoryId = $query->fetch();

        // Znajdź id sposobu płatności powiązanego z zalogowanym użytkownikiem
        $query = $db->prepare("SELECT id FROM payment_methods_assigned_to_users
            WHERE user_id = {$user_id} AND name = '{$payment_method}'
        ");
        $query->execute();

        $paymentId = $query->fetch();

        $query = $db->prepare("UPDATE expenses SET 
            expense_category_assigned_to_user_id = {$categoryId['id']},
            payment_method_assigned_to_user_id = {$paymentId['id']},
            amount = {$amount},
            date_of_expense = '{$date}',
            expense_comment = '{$comment}'
            WHERE id = {$expense_id}
        ");

        return $query->execute();
    }

    /** USUWANIE POJEDYNCZYCH PRZYCHODÓW/WYDATKÓW */

    /**
     * Usuń pojedynczy przychód/wydatek
     * 
     * @param int $idToDelete  Id przychodu lub wydatku
     * @param string $tableName  Nazwa tabeli, w której znajduje się dany przychód/wydatek
     * 
     * @return boolean  True, jeśli usuwanie zakończyło się pomyślnie, false w przeciwnym
     * wypadku
     */
    public function deleteIncomeOrExpense($idToDelete, $tableName)
    {
        $db = static::getDB();

        $query = $db->prepare("DELETE FROM {$tableName} WHERE id = {$idToDelete}");

        return $query->execute();
    }

    /** LIMITY WYDATKÓW */

    /**
     * Ustal limit wydatków w danej kategorii
     * 
     * @param int $user_id  Id zalogowanego użytkownika
     * @param string $category  Nazwa kategorii
     * @param float $amount  Kwota limitu
     * 
     * @return boolean  True, jeśli pomyślnie dodano limit, false w przeciwnym
     * wypadku
     */
    public function setExpenseLimit($user_id, $category, $amount)
    {
        $db = static::getDB();

        // Znajdź id kategorii powiązanej z danym użytkownikiem
        $query = $db->prepare("SELECT id FROM expenses_category_assigned_to_users
            WHERE user_id = {$user_id} AND name = '{$category}'
        ");
        $query->execute();
        $categoryId = $query->fetch();

        $query = $db->prepare("UPDATE expenses_category_assigned_to_users 
            SET limitation = {$amount} WHERE id = {$categoryId['id']}
        ");

        return $query->execute();
    }
}
