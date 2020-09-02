<?php

namespace App\Models;

use \App\Token;
use PDO;

/**
 * Model do zapamiętywania logowania
 * 
 * PHP v. 7+
 */
class RememberedLogin extends \Core\Model
{
    /**
     * Znajdź zapamiętane logowanie przy pomocy tokena
     * 
     * @param string $token  Token zapamiętanego logowania
     * 
     * @return mixed  Objekt RememberedLogin, jeśli znaleziono, w przeciwnym wypadku - false
     */
    public static function findByToken($token)
    {
        $token = new Token($token);
        $token_hash = $token->getHash();

        $sql = 'SELECT * FROM remembered_logins WHERE token_hash = :token_hash';

        $db = static::getDB();
        $statement = $db->prepare($sql);
        $statement->bindValue(':token_hash', $token_hash, PDO::PARAM_STR);

        $statement->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $statement->execute();

        return $statement->fetch();
    }

    /**
     * Pobierz model user powiązany z jego zapamiętanym logowaniem
     * 
     * @return User  model user
     */
    public function getUser()
    {
        return User::findByID($this->id);
    }

    /**
     * Sprawdź, czy token do zapamiętywania logowania wygasł
     * 
     * @return boolean  True, jeśli token wygasł, w przeciwnym wypadku - false
     */
    public function hasExpired()
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * Usuń model
     * 
     * @return void
     */
    public function delete()
    {
        $sql = 'DELETE FROM remembered_logins WHERE token_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':token_hash', $this->token_hash, PDO::PARAM_STR);

        $stmt->execute();
    }
}
