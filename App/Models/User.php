<?php

namespace App\Models;

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
}
