<?php

namespace App\Models;

use PDO;
use App\Token;
use Core\View;
use App\Mail;
use DateTime;

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
        if ($this->usernameExists($this->username, $this->id ?? null)) $this->errors[] = 'Podany login już istnieje w bazie danych';

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
     * @param int $ignore_id  Id użytkownika, przy którym wyszukiwanie loginu będzie
     * ignorowane w trybie edycji danych
     * 
     * @return boolean  True, jeśli istnieje rekord z danym loginem, false w przeciwnym wypadku
     */
    public static function usernameExists($username, $ignore_id = null)
    {
        $user = static::findByUsername($username);

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
                if (!$user->is_active) {
                    $_SESSION['login_failed'] = 'Aby móc korzystać z konta, musisz je aktywować';
                    $_SESSION['account_unactive'] = true;

                    return false;
                }

                if (isset($_SESSION['login_failed'])) unset($_SESSION['login_failed']);
                if (isset($_SESSION['account_unactive'])) unset($_SESSION['account_unactive']);

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
     * Rozpocznij proces resetowania hasła poprzez wygenerowanie nowego tokena
     * i czasu wygaśnięcia tej opcji
     * 
     * @return boolean  True, jeśli generowanie się powiodło, false w przeciwnym wypadku
     */
    protected function startPasswordReset()
    {
        $token = new Token();
        $hashed_token = $token->getHash();
        $this->password_reset_token = $token->getValue();

        $expiry_timestamp = time() + 60 * 60 * 2; // 2 godziny od teraz

        $db = static::getDB();

        $query = $db->prepare('UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id');
        $query->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $query->bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $query->execute();
    }

    /**
     * Wyślij instrukcje resetowania hasła w mailu do użytkownika
     * 
     * @return void
     */
    protected function sendPasswordResetEmail()
    {
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this->password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url, 'username' => $this->username]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url, 'username' => $this->username]);

        Mail::send($this->email, 'Zmiana hasla w Personal Budget Manager by Michael Slabikovsky', $text, $html);
    }

    /**
     * Wyślij instrukcje resetowania hasła do konkretnego użytkownika
     * 
     * @param string $email  Adres email użytkownika
     * 
     * @return void
     */
    public static function sendPasswordReset($email)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user->startPasswordReset()) {
                $user->sendPasswordResetEmail();
            }
        }
    }

    /**
     * Znajdź model user przy pomocy tokena resetu hasła i czasu wygaśnięcia
     * 
     * @param string $token  Token resetu hasła wysłany do użytkownika
     * 
     * @return mixed  Obiekt User, jeśli znaleziono i token nie wygasł, null w przeciwnym
     * wypadku
     */
    public static function findByPasswordReset($token)
    {
        $token = new Token($token);
        $hashed_token = $token->getHash();

        $db = static::getDB();

        $query = $db->prepare('SELECT * FROM users
                WHERE password_reset_hash = :token_hash');

        $query->bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $query->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $query->execute();

        $user = $query->fetch();

        if ($user) {
            // Sprawdź czy token nie wygasł
            if (strtotime($user->password_reset_expires_at) > time()) {
                return $user;
            }
        }
    }

    /**
     * Zresetuj hasło
     * 
     * @param string $password  Nowe hasło
     * 
     * @return boolean  True, jeśli pomyślnie zaktualizowano hasło, w przeciwnym wypadku - false
     */
    public function resetPassword($password, $passwordConfirmation)
    {
        $this->password = $password;
        $this->passwordConfirmation = $passwordConfirmation;
        $this->surname = 'To dla zmylenia "przeciwnika" :)';

        $this->validate();

        if (empty($this->errors)) {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $db = static::getDB();

            $query = $db->prepare('UPDATE users
                    SET password = :password_hash,
                        password_reset_hash = NULL,
                        password_reset_expires_at = NULL
                    WHERE id = :id');
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $query->execute();
        }

        return false;
    }

    /**
     * Wyślij e-maila zawierającego link aktywacyjny
     * 
     * @return void
     */
    public function sendActivationEmail()
    {
        if (isset($_SESSION['resenend_activation_email'])) {
            $token = new Token();
            $hashed_token = $token->getHash();
            $this->activation_token = $token->getValue();

            $db = static::getDB();
            $query = $db->prepare('UPDATE users SET activation_hash = :hashed_token WHERE id = :id');
            $query->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
            $query->bindValue(':id', $this->id, PDO::PARAM_INT);
            $query->execute();

            unset($_SESSION['resenend_activation_email']);
        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this->activation_token;

        // Treść wiadomości - zwykły tekst i HTML
        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this->email, 'Aktywacja konta na Personal Budget Manager by Michael Slabikovsky', $text, $html);
    }

    /**
     * Aktywuj konto użytkownika z określonym tokenem aktywacyjnym
     * 
     * @param string $value  Token aktywacyjny z URL-a
     * 
     * @return void
     */
    public static function activate($value)
    {
        $token = new Token($value);
        $hashed_token = $token->getHash();

        $db = static::getDB();

        $query = $db->prepare('
            UPDATE users
            SET is_active = 1, activation_hash = null
            WHERE activation_hash = :hashed_token
        ');
        $query->bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);
        $query->execute();
    }

    /**
     * Dodaj tymczasowo nowe dane użytkownika do odzielnej tabeli.
     * Dane te zostaną później przeniesione do tabeli "users" po uprzednim
     * potwierdzeniu przez użytkownika
     * 
     * @param string $username  Login użytkownika
     * @param string $email  Wiadomo
     * @param string $firstName  Imię użytkownika
     * @param string $oldPassword  Aktualne hasło
     * @param string $newPassword  Nowe hasło
     * @param string $newPasswordConfirmation  Potwierdzenie nowego hasła
     * 
     * @return boolean  True, jeśli pomyślnie zapisano dane do tabeli,
     * w przeciwnym przypadku - false
     */
    public function saveNewDataTemporarily($username, $email, $firstName, $oldPassword, $newPassword, $newPasswordConfirmation)
    {
        $token = new Token();
        $hashed_token = $token->getHash();

        $db = static::getDB();

        if ($username == "") $username = $this->username;

        if ($email == "") $email = $this->email;

        if ($firstName == "") $firstName = $this->name;

        $expiry_timestamp = new DateTime();
        $expiry_timestamp->modify('+1 hour');
        $expiry_timestamp_formated = $expiry_timestamp->format('Y-m-d H:i:s');

        if ($oldPassword == "") {
            if ($this->validateInEditMode($username, $email)) {
                $query = $db->prepare("INSERT INTO data_change (id, name, username, email, token_hash, expires_at) VALUES (
                    {$this->id},
                    '{$firstName}',
                    '{$username}',
                    '{$email}',
                    '{$hashed_token}',
                    '{$expiry_timestamp_formated}'
                    )
                ");
                return $query->execute();
            } else {
                return false;
            }
        } else {
            if ($this->validateInEditMode($username, $email, $oldPassword, $newPassword, $newPasswordConfirmation)) {
                $query = $db->prepare("INSERT INTO data_change VALUES (
                    {$this->id},
                    '{$firstName}',
                    '{$username}',
                    '{$newPassword}',
                    '{$email}',
                   '{$hashed_token}',
                    {$expiry_timestamp_formated}
                    )
                ");
                return $query->execute();
            } else {
                return false;
            }
        }
    }

    /**
     * Zmień dane użytkownika
     * 
     * @param string $username  Login użytkownika
     * @param string $email  Wiadomo
     * @param string $firstName  Imię użytkownika
     * @param string $oldPassword  Aktualne hasło
     * @param string $newPassword  Nowe hasło
     * @param string $newPasswordConfirmation  Potwierdzenie nowego hasła
     * 
     * @return boolean  True, jeśli zmiana się powiodła, w przeciwnym przypadku - false
     */
    public function changeUserData($username, $email, $firstName, $oldPassword, $newPassword, $newPasswordConfirmation)
    {
        $db = static::getDB();

        if ($username == "") $username = $this->username;

        if ($email == "") $email = $this->email;

        if ($firstName == "") $firstName = $this->name;

        if ($oldPassword == "") {
            if ($this->validateInEditMode($username, $email)) {
                $query = $db->prepare("
                    UPDATE users 
                    SET name = '{$firstName}', username = '{$username}', email = '{$email}'
                    WHERE id = {$this->id}
                ");

                return $query->execute();
            } else {
                return false;
            }
        } else {
            if ($this->validateInEditMode($username, $email, $oldPassword, $newPassword, $newPasswordConfirmation)) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                $query = $db->prepare("
                    UPDATE users 
                    SET name = '{$firstName}', username = '{$username}',
                    email = '{$email}', password = '{$newPasswordHash}'
                    WHERE id = {$this->id}'
                ");

                return $query->execute();
            } else {
                return false;
            }
        }
    }

    /**
     * Sprawdź poprawność wprowadzonych danych podczas edycji
     * 
     * @param string $username  Login użytkownika
     * @param string $email  Wiadomo
     * @param string $firstName  Imię użytkownika
     * @param string $oldPassword  Aktualne hasło
     * @param string $newPassword  Nowe hasło
     * @param string $newPasswordConfirmation  Potwierdzenie nowego hasła
     * 
     * @return boolean  True, jeśli dane są prawidłowe, w przeciwnym wypadku - false
     */
    private function validateInEditMode($username, $email, $oldPassword = "", $newPassword = "", $newPasswordConfirmation = "")
    {
        if (static::usernameExists($username, $this->id)) $this->errors[] = "Podany login już istnieje w bazie danych";

        if (static::emailExists($email, $this->id)) $this->errors[] = "Podany adres e-mail już istnieje w bazie danych";

        if ($oldPassword != "" && $newPassword != "" && $newPasswordConfirmation != "") {
            if (!password_verify($oldPassword, $this->password)) {
                $this->errors[] = "Nieprawidłowe hasło";
            } else {
                if ($newPassword != $newPasswordConfirmation) {
                    $this->errors[] = "Nowe hasła w obu polach muszą być takie same";
                }
            }
        }

        if (empty($this->errors)) return true;
        else return false;
    }
}
