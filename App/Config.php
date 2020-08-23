<?php

namespace App;

/**
 * Plik konfiguracyjny aplikacji
 * 
 * PHP v. 7+
 */
class Config
{
    /**
     * Host bazy danych
     */
    const DB_HOST = 'localhost';

    /**
     * Nazwa bazy
     * 
     * @var string
     */
    const DB_NAME = 'personal-budget';

    /**
     * Admin bazy
     * @var string
     */
    const DB_USER = 'root';

    const DB_PASSWORD = '';

    /**
     * Pokaż lub ukryj komunikaty o błędach na ekranie
     * @var boolean
     */
    const SHOW_ERRORS = true;

    /**
     * Sekretny klucz do hashowania
     * @var string
     */
    const SECRET_KEY = '';
}
