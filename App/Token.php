<?php

namespace App;

/**
 * Unikalne losowe tokeny
 * 
 * PHP v 7+
 */
class Token
{
    /**
     * Wartość tokena
     * @var array
     */
    protected $token;

    /**
     * Konstruktor klasy. Tworzenie nowego losowego tokena
     * 
     * @retrun void
     */
    public function __construct($token_value = null)
    {
        if ($token_value) $this->token = $token_value;
        else $this->token = bin2hex(random_bytes(16)); // 16 bajtów = 128 bitów = 32 znaki heksadecymalne
    }

    /**
     * Pobierz wartość tokena
     * 
     * @return string
     */
    public function getValue()
    {
        return $this->token;
    }

    /**
     * Pobierz zahashowaną wartość tokena
     * 
     * @return string  Zahashowana wartość
     */
    public function getHash()
    {
        return hash_hmac('sha256', $this->token, \App\Config::SECRET_KEY); // sha256 = 64 znaki
    }
}
