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
     * Kategorie przychodów/wydatków/sposoby płatności
     * 
     * @var array  Tablica asocjacyjna z tłumaczeniami kategorii
     */
    const TRANSLATED_CATEGORIES = [
        'Salary' => 'Wynagrodzenie',
        'Interest' => 'Odsetki bankowe',
        'Allegro' => 'Sprzedaż na allegro',
        'Another-Incomes' => 'Inne',
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
        'Courses' => 'Szkolenia',
        'Cash' => 'Gotówka',
        'Debit-Card' => 'Karta debetowa',
        'Credit-Card' => 'Karta kredytowa'
    ];

    /**
     * Przetłumacz kategorię (jedną z domyślnych) na język polski
     * 
     * @param string $category  Kategoria przychodu/wydatku lub sposób płatności
     * 
     * @return string  Przetłumaczona kategoria
     */
    public static function translateCategory($category)
    {
        foreach (static::TRANSLATED_CATEGORIES as $key => $value) {
            if ($category == $key) {
                return $value;
            }
        }

        return $category;
    }
}
