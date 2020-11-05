<?php

namespace Core;

class View
{
    /**
     * Wyrenderuj plik widoku
     * 
     * @param string $view  Plik widoku
     * @param array  Tablica asocjacyjna z danymi do wyświetlenia w widoku (opcjonalnie)
     * 
     * @return void
     */
    public static function render($view, $args = [])
    {
        extract($args, EXTR_SKIP);

        $file = dirname(__DIR__) . "/App/Views/$view";  // względnie do folderu Core

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }

    /**
     * Wyrenderuj szablon widoku z użyciem silnika Twig
     * 
     * @param string $template  Plik templatki
     * @param array $args  Tablica asocjacyjna z danymi do wyświetlenia w widoku (opcjonalnie)
     * 
     * @return void
     */
    public static function renderTemplate($template, $args = [])
    {
        echo static::getTemplate($template, $args);
    }

    /**
     * Pobierz treści szblonu widoku z użyciem silnika Twig
     * 
     * @param string $template  Plik templatki
     * @param array $args  Tablica asocjacyjna z danymi do wyświetlenia w widoku (opcjonalnie)
     * 
     * @return string
     */
    public static function getTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig\Environment($loader);
            $twig->addGlobal('session', $_SESSION);
            $twig->addGlobal('flash_messages', \App\Flash::getMessages());
            $twig->addGlobal('errors', \App\Models\User::getErrors());
        }

        return $twig->render($template, $args);
    }
}
