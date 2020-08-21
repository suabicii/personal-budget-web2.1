<?php

namespace Core;

abstract class Controller
{
    /**
     * Parametry z dopasowanej trasy
     * @var array
     */
    protected $route_params = [];

    /**
     * Konstruktor klasy
     * 
     * @param array $route_params  Parametry z trasy
     * 
     * @return void
     */
    public function __construct($route_params)
    {
        $this->route_params = $route_params;
    }

    /**
     * Magiczna metoda wywoływana, gdy wywołuje się nieistniejącą
     * lub niedostępną metodę na objekcie tej klasy. Używana do wykonywania filtrów
     * before i after na metodach akcji. Metody akcji muszą być nazwane sufiksem "Action",
     * np. indexAction, showAction etc.
     * 
     * @param string $name  Nazwa metody
     * @param array $args  Argumenty przepuszczane przez metodę
     * 
     * @return void 
     */
    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Filtr before - wywoływany przed metodą akcji
     * 
     * @return void
     */
    protected function before()
    {
    }

    /**
     * Filtr after - wywoływany po metodzie akcji
     * 
     * @return void
     */
    protected function after()
    {
    }

    /**
     * Przekieruj do innej strony
     * 
     * @param string $url  Relatywny URL
     * 
     * @return void
     */
    public function redirect($url)
    {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit;
    }
}
