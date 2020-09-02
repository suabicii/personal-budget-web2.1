<?php

namespace App;

use Core\View;
// Importuj klasy PHPMailera do globalnej przestrzeni nazw
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Wysyłanie maili do aktywacji kont
 * 
 * PHP v. 7+
 */
class Mail
{
    public static function send($to, $subject, $text, $html)
    {
        // Tworzenie instancji. Przekazywanie „true” włącza wyjątki
        $mail = new PHPMailer(true);

        try {
            //Ustawienia serwera
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Włącz szczegółowe wyjście debugowania
            $mail->isSMTP();                                            // Wyślij z użyciem SMTP
            $mail->Host       = 'smtp1.slabikovsky.com';                    // Ustaw serwer SMTP do przesyłania
            $mail->SMTPAuth   = true;                                   // Włącz uwierzytelnianie SMTP
            $mail->Username   = 'user@slabikovsky.com';                 // Nazwa użytkownika SMTP
            $mail->Password   = 'secret';                               // Hasło SMTP
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Włącz szyfrowanie TLS; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->Port       = 587;                                    // Port TCP do połączeniua, użyj 465 do `PHPMailer::ENCRYPTION_SMTPS` powyżej

            //Adresat
            $mail->setFrom('no-reply@slabikovsky.com', 'Michael Slabikovsky');
            $mail->addAddress($to); // Dodaj adresata. Imię jest opcjonalne

            // Treść
            $mail->isHTML(true);  // Ustaw format maila na HTML
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;

            // Ustawienie języka na polski
            $mail->setLanguage('pl', dirname(__DIR__) . '/vendor/phpmailer/phpmailer/language/');

            $mail->send();
        } catch (Exception $e) {
            echo "Nie można wysłać wiadomości. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
