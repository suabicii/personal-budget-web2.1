<?php

/**
 * Front controller
 * 
 * PHP v. 7.4
 */

ini_set('session.cookie_lifetime', '864000'); // 10 dni w sekundach

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Obsługa błędów i wyjątków
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

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
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('password/reset/reset-password', ['controller' => 'Password', 'action' => 'reset-password']);
$router->add('settings/confirm/{token:[\da-f]+}', ['controller' => 'Settings', 'action' => 'confirm']);
$router->add('settings/confirm/confirm-edit', ['controller' => 'Settings', 'action' => 'confirm-edit']);
$router->add('login', ['controller' => 'Login', 'action' => 'create']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('home', ['controller' => 'Home', 'action' => 'index']);
$router->add('add-income', ['controller' => 'Income', 'action' => 'index']);
$router->add('add-expense', ['controller' => 'Expense', 'action' => 'index']);
$router->add('balance', ['controller' => 'Balance', 'action' => 'index']);
$router->add('settings', ['controller' => 'Settings', 'action' => 'index']);
$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);
