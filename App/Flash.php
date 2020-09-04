<?php

namespace App;

/**
 * Komunikaty-migawki: komunikaty wyświetlane jednorazowo z użyciem sesji
 * do przechowywania pomiędzy żądaniami
 * 
 * PHP 7+
 */
class Flash
{
    // Typy komunikatów
    const SUCCESS = 'success';
    const INFO = 'info';
    const WARNING = 'warning';

    /**
     * Dodaj komunikat
     * 
     * @param string $message  Treść komunikatu
     * @param string $type  Typ
     * 
     * @return void
     */
    public static function addMessage($message, $type = 'success')
    {
        // Utwórz tablicę w sesji, jeśli jeszcze nie istnieje
        if (!isset($_SESSION['flash_notifications'])) $_SESSION['flash_notifications'] = [];

        // Dodaj komunikat do tablicy
        $_SESSION['flash_notifications'][] = [
            'body' => $message,
            'type' => $type
        ];
    }

    /**
     * Pobierz wszystkie komunikaty
     * 
     * @return mixed  Tablica ze wszystkimi komunikatami lub null, jeśli nie ma żadnego
     */
    public static function getMessages()
    {
        if (isset($_SESSION['flash_notifications'])) {
            $messages = $_SESSION['flash_notifications'];
            unset($_SESSION['flash_notifications']);

            return $messages;
        }
    }
}
