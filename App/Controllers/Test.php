<?php

namespace App\Controllers;

use App\Categories;

class Test extends \Core\Controller
{
    public function indexAction()
    {
        echo Categories::translateCategory('Salary');
    }
}
