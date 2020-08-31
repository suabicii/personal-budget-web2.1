<?php

/**
 * Front controller
 * 
 * PHP v. 7+
 */

ini_set('session.cookie_lifetime', '864000');

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Obsługa błędów i wyjątków
 */

/**
 * Miejsce na obsługę błędów
 * 
 */

/**
 * Sesje
 */
session_start();

/**
 * Routing
 */
$router = new Core\Router();

// Dodawanie tras
$router->add('', ['controller' => 'Start', 'action' => 'index']);
$router->add('create', ['controller' => 'Signup', 'action' => 'create']);
$router->add('login', ['controller' => 'Login', 'action' => 'create']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('home', ['controller' => 'Home', 'action' => 'index']);
$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);
