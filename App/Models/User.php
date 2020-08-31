<?php

namespace App\Models;

use PDO;

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
     * @return mixed  Objekt User, jeśli znaleziono, false w przeciwnym wypadku
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
     * @return mixed  Objekt User, jeśli znaleziono, false w przeciwnym wypadku
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
     * @return mixed  Objekt User, jeśli znaleziono, w przeciwnym wypadku - false
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

            /**
             * Miejsce na token
             * 
             */

            $sql = 'INSERT INTO users VALUES (NULL, :name, :login, :password, :email)';

            $db = static::getDB();
            $statement = $db->prepare($sql);

            $statement->bindValue(':name', $this->name, PDO::PARAM_STR);
            $statement->bindValue(':login', $this->username, PDO::PARAM_STR);
            $statement->bindValue(':password', $password_hash, PDO::PARAM_STR);
            $statement->bindValue(':email', $this->email, PDO::PARAM_STR);

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
     * @return mixed  Objekt user lub false, jeśli autoryzacja się nie powiedzie
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
}
