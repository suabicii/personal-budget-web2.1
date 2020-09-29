<?php

namespace App;

/**
 * Kategorie przychodów i wydatków oraz metody płatności
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
        'Another-Incomes' => 'Inne'
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
        'For-Retirement' => 'Na złotą jesień, czyli emeryturę',
        'Debt-Repayment' => 'Spłata długów',
        'Gift' => 'Darowizna',
        'Another-Expenses' => 'Inne wydatki',
        'Courses' => 'Szkolenia'
    ];

    /**
     * Sposoby płatności
     * 
     * @var array
     */
    const PAYMENT_METHODS = [
        'Cash' => 'Gotówka',
        'Debit-Card' => 'Karta debetowa',
        'Credit-Card' => 'Karta kredytowa'
    ];
}
