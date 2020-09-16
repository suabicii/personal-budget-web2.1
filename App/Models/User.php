<?php

namespace App\Models;

use PDO;
use App\Token;
use Core\View;
use App\Mail;

class User extends \Core\Model
{
    /**
     * Komunikaty o błędach
     * 
     * @var array
     */
    public $errors = [];

    /**
     * Konstruktor klasy
     * 
     * @param array $data  Początkowe wartości właściwości (Opcjonalnie)
     * 
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Walidacja bieżących wartości właściwości, dodawanie komunikatów o błędach
     * do tablicy "errors"
     * 
     * @return void
     */
    public function validate()
    {
        // Walidacja imienia i nazwiska
        if ($this->name == '') $this->errors[] = 'Podaj imię';
        if ($this->surname == '') $this->errors[] = 'Podaj nazwisko';

        // Walidacja loginu
        if ($this->usernameExists($this->username)) $this->errors[] = 'Podany login już istnieje w bazie danych';

        // Walidacja adresu e-mail
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) $this->errors[] = 'Niepoprawny format';
        if (static::emailExists($this->email, $this->id ?? null)) $this->errors[] = 'Podany adres e-mail już istnieje w bazie danych';

        // Walidacja hasła
        if (isset($this->password)) {
            if (strlen($this->password) < 6) $this->errors[] = 'Hasło musi składać się z co najmniej 6 znaków';
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) $this->errors[] = 'Hasło musi posiadać co najmniej jedną literę';
            if (preg_match('/.*\d+.*/i', $this->password) == 0) $this->errors[] = 'Hasło musi składać się z co najmniej jednej cyfry';
            if ($this->password != $this->passwordConfirmation) $this->errors[] = 'Hasła w obu polach muszą być takie same';
        }
    }

    /**
     * Sprawdź, czy dany adres e-mail już istnieje w bazie danych
     * 
     * @param string $email wiadomo :)
     * @param int $ignore_id  Id użytkownika, przy którym wyszukiwanie maila będzie
     * ignorowane w trybie edycji danych
     * 
     * @return boolean  True, jeśli istnieje rekord z danym mailem, false w przeciwnym wypadku
     */
    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sprawdź, czy dany login już istnieje w bazie danych
     * 
     * @param string $username  Login
     * 
     * @return boolean  True, jeśli istnieje rekord z danym loginem, false w przeciwnym wypadku
     */
    public static function usernameExists($username)
    {
        $user = static::findByUsername($username);

        if ($user) return true;

        return false;
    }

    /**
     * Znajdź użytkownika po mailu
     * 
     * @return mixed  Obiekt User, jeśli znaleziono, false w przeciwnym wypadku
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindValue(':email', $email, PDO::PARAM_STR);

        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Znajdź użytkownika po loginie
     * 
     * @return mixed  Obiekt User, jeśli znaleziono, false w przeciwnym wypadku
     */
    public static function findByUsername($username)
    {
        $sql = 'SELECT * FROM users WHERE username = :username';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindValue(':username', $username, PDO::PARAM_STR);

        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Znajdź użytkownika po ID
     * 
     * @param int $id  ID użytkownika
     * 
     * @return mixed  Obiekt User, jeśli znaleziono, w przeciwnym wypadku - false
     */
    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);

        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Pobierz listę błędów
     * 
     * @return array  Tablica, zawierająca błędy
     */
    public static function getErrors()
    {
        if (isset($_SESSION['errors'])) {
            $errors = $_SESSION['errors'];
            unset($_SESSION['errors']);
            return $errors;
        }
    }

    /**
     * Oblicz ilość wierszy z tabeli
     * 
     * @param string $tableName
     * 
     * @return int  Ilość wierszy
     */
    public static function getAmountOfTableRows($tableName)
    {
        $sql = "SELECT * FROM {$tableName}";

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->execute();

        return $statement->rowCount();
    }

    /**
     * Przypisuj kategorie przychodów/wydatków/metody płatności do nowo utworzonego konta
     * 
     * @param string $tableWithCategories  Tabela, w której znajdą się poszczególne kategorie/metody
     * @param string $tableForAssign  Tabela z kategoriami przypisanymi do użytkownika
     * @param string $username  Login
     * @param int $amountOfTableRows  Ilość wierszy w tabeli z sufiksem "default"
     * 
     * @return void
     */
    public static function assignCategoriesToUser($tableWithCategories, $tableForAssign, $username, $amountOfTableRows)
    {
        for ($i = 1; $i <= $amountOfTableRows; $i++) {
            $db = static::getDB();
            $statement = $db->prepare("INSERT INTO {$tableForAssign}
            VALUES (
                NULL,
                (SELECT id FROM users WHERE username = :username),
                (SELECT name FROM {$tableWithCategories} WHERE id = :categoryId)
            )");
            $statement->execute([
                ":username" => $username,
                ":categoryId" => $i
            ]);
        }
    }

    /**
     * Zapisz model "user" z bieżącymi wartościami właściwości
     * 
     * @return boolean  True, jeśli użytkownik został zapisany, w przeciwnym wypadku false
     */
    public function save()
    {
        $this->validate();

        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $sql = 'INSERT INTO users (name, username, password, email, activation_hash) VALUES (:name, :login, :password, :email, :activation_hash)';

            $db = static::getDB();
            $statement = $db->prepare($sql);

            $statement->bindValue(':name', $this->name, PDO::PARAM_STR);
            $statement->bindValue(':login', $this->username, PDO::PARAM_STR);
            $statement->bindValue(':password', $password_hash, PDO::PARAM_STR);
            $statement->bindValue(':email', $this->email, PDO::PARAM_STR);
            $statement->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            $userAdded = $statement->execute();

            // Przypisanie kategorii do użytkownika
            $amountOfTableRows = static::getAmountOfTableRows('incomes_category_default');
            static::assignCategoriesToUser('incomes_category_default', 'incomes_category_assigned_to_users', $this->username, $amountOfTableRows);

            $amountOfTableRows = static::getAmountOfTableRows('expenses_category_default');
            static::assignCategoriesToUser('expenses_category_default', 'expenses_category_assigned_to_users', $this->username, $amountOfTableRows);

            $amountOfTableRows = static::getAmountOfTableRows('payment_methods_default');
            static::assignCategoriesToUser('payment_methods_default', 'payment_methods_assigned_to_users', $this->username, $amountOfTableRows);

            return $userAdded;
        }

        return false;
    }

    /**
     * Autoryzacja użytkownika z użyciem loginu i hasła
     * 
     * @param string $username  Login
     * @param string $password
     * 
     * @return mixed  Obiekt user lub false, jeśli autoryzacja się nie powiedzie
     */
    public static function authenticate($username, $password)
    {
        $user = static::findByUsername($username);

        if ($user) {
            if (password_verify($password, $user->password)) {
                if (isset($_SESSION['login_failed'])) unset($_SESSION['login_failed']);
                return $user;
            }
        }

        $_SESSION['login_failed'] = 'Nieprawidłowy login lub hasło';

        return false;
    }

    /**
     * Zapamiętaj logowanie porzez wstawienie unikalnego tokena do tabeli
     * remembered_logins dla danego rekordu z użytkownikiem
     * 
     * @return boolean  True, jeśli logowanie zostało zapamiętane z powodzeniem,
     * false w przeciwnym wypadku
     */
    public function rememberLogin()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->remember_token = $token->getValue();

        $this->expiry_timestamp = time() + 60 * 60 * 24 * 30; // 30 dni od teraz

        $sql = 'INSERT INTO remembered_logins VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $statement = $db->prepare($sql);

        $statement->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $statement->bindValue(':user_id', $this->id, PDO::PARAM_INT);
        $statement->bindValue(':expires_at', date('Y-m-d H:i:s', $this->expiry_timestamp), PDO::PARAM_STR);

        return $statement->execute();
    }

    /**
     * Wyślij e-maila zawierającego link aktywacyjny
     * 
     * @return void
     */
    public function sendActivationEmail()
    {
        $url = "http://{$_SERVER['HTTP_HOST']}/activate/{$this->activation_token}";

        // Treść wiadomości - zwykły tekst i HTML
        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Aktywacja konta na Personal Budget Manager by Michael Slabikovsky', $text, $html);
    }

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
    public function addIncomeToDatabase($amount, $category, $date, $comment = '')
    {
        if ($this->validateIncomeOrExpenseData($amount, $category, $date)) {
            $db = static::getDB();

            $query = $db->prepare("SELECT id FROM incomes_category_assigned_to_users WHERE user_id = :user_id AND name = :name");
            $query->execute([
                ":user_id" => $this->id,
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
                ":user_id" => $this->id,
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
    public function addExpenseToDatabase($amount, $payment, $category, $date, $comment = '')
    {
        $_SESSION['adding_expenses'] = true;

        if ($this->validateIncomeOrExpenseData($amount, $category, $date, $payment)) {
            unset($_SESSION['adding_expenses']);

            $db = static::getDB();

            $query = $db->prepare("SELECT id FROM expenses_category_assigned_to_users WHERE user_id = :user_id AND name = :name");
            $query->execute([
                ":user_id" => $this->id,
                ":name" => $category
            ]);
            $category_id = $query->fetch();

            $query = $db->prepare("SELECT id FROM payment_methods_assigned_to_users WHERE user_id = :user_id AND name = :name");
            $query->execute([
                ":user_id" => $this->id,
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
                ":user_id" => $this->id,
                ":category_assigned_to_user_id" => $category_id['id'],
                "payment_assigned_to_user_id" => $payment_id['id'],
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
    public function getSummedIncomes($startDate, $endDate)
    {
        $db = static::getDB();

        $income_category = 'income_category_assigned_to_user_id';

        $query = $db->prepare("
            SELECT {$income_category}, name, SUM(amount) as amount FROM incomes, incomes_category_assigned_to_users 
            WHERE incomes.user_id = {$this->id} AND incomes_category_assigned_to_users.id = incomes.{$income_category}
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
    public function getSummedExpenses($startDate, $endDate)
    {
        $db = static::getDB();

        $expense_category = 'expense_category_assigned_to_user_id';

        $query = $db->prepare("SELECT {$expense_category}, name, SUM(amount) as amount FROM expenses, expenses_category_assigned_to_users WHERE expenses.user_id = {$this->id} AND expenses_category_assigned_to_users.id = expenses.{$expense_category} AND date_of_expense BETWEEN '{$startDate}' AND '{$endDate}' GROUP BY {$expense_category} ORDER BY amount DESC");
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
    public function getAllIncomes($startDate, $endDate)
    {
        $db = static::getDB();

        $income_category = 'income_category_assigned_to_user_id';

        $query =  $db->prepare("SELECT incomes.user_id, income_category_assigned_to_user_id, name, amount, date_of_income, income_comment FROM incomes, incomes_category_assigned_to_users WHERE incomes_category_assigned_to_users.id = incomes.{$income_category} AND incomes.user_id = {$this->id} AND date_of_income BETWEEN '{$startDate}' AND '{$endDate}' ORDER BY amount DESC");
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
    public function getAllExpenses($startDate, $endDate)
    {
        $db = static::getDB();

        $expense_category = 'expense_category_assigned_to_user_id';

        $query =  $db->prepare("SELECT expenses.user_id, expense_category_assigned_to_user_id, name, amount, date_of_expense, expense_comment FROM expenses, expenses_category_assigned_to_users WHERE expenses_category_assigned_to_users.id = expenses.{$expense_category} AND expenses.user_id = {$this->id} AND date_of_expense BETWEEN '{$startDate}' AND '{$endDate}' ORDER BY amount DESC");
        $query->execute();

        return $query->fetchAll();
    }
}
