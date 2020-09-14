<?php

namespace App;

/**
 * Kategorie przychodów i wydatków
 * 
 * PHP v. 7.4
 */
class Categories
{
    /**
     * Kategorie przychodów
     * 
     * @var array
     */
    const INCOMES_CATEGORIES = [
        'Salary' => 'Wynagrodzenie',
        'Interest' => 'Odsetki bankowe',
        'Allegro' => 'Sprzedaż na allegro',
        'Another' => 'Inne'
    ];

    /**
     * Kategorie wydatków
     * 
     * @var array
     */
    const EXPENSES_CATEGORIES = [
        'Transport' => 'Transport',
        'Books' => 'Książki',
        'Food' => 'Jedzenie',
        'Apartments' => 'Mieszkanie',
        'Telecommunication' => 'Telekomunikacja',
        'Health' => 'Opieka zdrowotna',
        'Clothes' => 'Ubranie',
        'Hygiene' => 'Higiena',
        'Kids' => 'Dzieci',
        'Recreation' => 'Rozrywka',
        'Trip' => 'Wycieczka',
        'Savings' => 'Oszczędności',
        'For Retirement' => 'Na złotą jesień, czyli emeryturę',
        'Debt Repayment' => 'Spłata długów',
        'Gift' => 'Darowizna',
        'Another' => 'Inne wydatki',
        'Courses' => 'Szkolenia'
    ];
}
