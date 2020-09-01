<?php

namespace Core;

/**
 * Obsługa błędów i wyjątków
 * 
 * PHP v. 7+
 */
class Error
{

    /**
     * Error handler. Przekonwertuj wszystkie błędy do wyjątków przy pomocy ErrorException
     * 
     * @param int $level  Poziom błędu
     * @param string $message  Komunikat o błędzie
     * @param string $file  Nazwa pliku, w którym pojawia się błąd
     * @param int $line  Numer lini w danym pliku
     * 
     * @return void
     */
    public static function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Exception handler
     * 
     * @param Exception $exception  Wyjątek
     * 
     * @return void
     */
    public static function exceptionHandler($exception)
    {
        // Kod to 404 (nie znaleziono) lub 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        if (\App\Config::SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

            $message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();

            error_log($message);

            View::renderTemplate("$code.html");
        }
    }
}
