<?php

namespace App\Controllers;

/**
 * Bazowy kontroler do uwierzytelniania
 * 
 * PHP v. 7+
 */
abstract class Authenticated extends \Core\Controller
{
    /**
     * Wymagaj logowania przed dostępem do poniższych metod w kontrolerze
     * 
     * @return void
     */
    protected function before()
    {
        $this->requireLogin();
    }
}
