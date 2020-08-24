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

        // Walidacja adresu e-mail
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) $this->errors[] = 'Niepoprawny format';
        if (static::emailExists($this->email, $this->id ?? null)) $this->errors[] = 'Podany adres e-mail już istnieje w bazie danych';

        // Walidacja hasła
        if (isset($this->password)) {
            if (strlen($this->password) < 6) $this->errors[] = 'Hasło musi składać się co najmniej z 6 znaków';
            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) $this->errors[] = 'Hasło musi posiadać co najmniej jedną literę';
            if (preg_match('/.*\d+.*/i', $this->password) == 0) $this->errors[] = 'Hasło musi składać się z co najmniej jednej cyfry';
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

            $sql = 'INSERT INTO users VALUES (NULL, :name, :surname, :email_posted, :login_posted, :password)';

            $db = static::getDB();
            $statement = $db->prepare($sql);

            $statement->bindValue(':name', $this->name, PDO::PARAM_STR);
            $statement->bindValue(':surname', $this->surname, PDO::PARAM_STR);
            $statement->bindValue(':email_posted', $this->email, PDO::PARAM_STR);
            $statement->bindValue(':login_posted', $this->login, PDO::PARAM_STR);
            $statement->bindValue(':password', $password_hash, PDO::PARAM_STR);

            return $statement->execute();
        }

        return false;
    }
}
