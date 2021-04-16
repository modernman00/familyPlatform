<?php 

/**
 * $date is the date from the database in the format 20-03-21
 * $addBy - could be in days, months, years [ 2 days, 2 months, 2 years]
 * $add_or_sub - two options date_add OR date_sub
 */

function modifyDate($date, string $addBy, callable $add_or_sub)
{
    $date = (date_create($date));
    $daySeven = $add_or_sub($date, date_interval_create_from_date_string($addBy));
    return [
        'date'=> date_format($daySeven, "Y-m-d"), 
        'fullDate' => date_format($daySeven, " jS \of F Y") 
    ];
}
