<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = \Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__));
$dotenv->load();

/**
 * Wysyłanie maili do aktywacji kont lub resetowania haseł
 * 
 * PHP v. 7.4
 */
class Mail
{
    public static function send($to, $subject, $text, $html)
    {
        $mail = new PHPMailer(true); // Tworzenie instancji i przekazywanie „true” włącza wyjątki

        try {
            //Ustawienia serwera
            $mail->isSMTP();                                            // Wyślij używając SMTP
            $mail->Host       = 'smtp.gmail.com';                    // Ustaw serwer SMTP do przesyłania
            $mail->SMTPAuth   = true;                                   // Włącz uwierzytelnianie SMTP
            $mail->Username   = 'personal.budget.slabikovsky@gmail.com';                     // nazwa użytkownika SMTP
            $mail->Password   = getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Włącz szyfrowanie SMTP; 
            $mail->Port       = 465;                                    // Port TCP do połączenia

            //Adresat
            $mail->setFrom('no-reply@michael.slabikovsky.com', 'Michael Slabikovsky');
            $mail->addAddress($to);     // Dodaj adresata

            // Treść wiadomości
            $mail->isHTML(true);                                  // Ustaw format na HTML
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text; // Wersja tekstowa (bez HTML-a)

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
