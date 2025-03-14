<?php

/**
 * $date is the date from the database in the format 20-03-21
 * $addBy - could be in days, months, years [ 2 days, 2 months, 2 years]
 * $add_or_sub - two options date_add OR date_sub
 * it returns an array [date, fullDate]
 *
 * @return string[]
 *
 * @psalm-return array{date: string, fullDate: string}
 */
function modifyDate($date, string $addBy, callable $add_or_sub): array
{
    $date = (date_create($date));
    $daySeven = $add_or_sub($date, date_interval_create_from_date_string($addBy));
    return [
        'date' => date_format($daySeven, "Y-m-d"),
        'fullDate' => date_format($daySeven, " jS \of F Y")
    ];
}

function dateDifference($date1, $date2): string
{
    $createDate1 = date_create($date1);
    $createDate2 = date_create($date2);
    $diff = date_diff($createDate1, $createDate2);
    return $diff->format("%R%a days");
}

/**
 * The DateInterval represents the differences between two dates in the year, month, day, hour, etc. To format the difference, you use the DateInterval‘s format. For example: 31 years, 6 months, 14 days
 */

function dateDiff($createDate1, $createDate2): string 
{   
    $dob = new DateTime($createDate1);
    $to_date = new DateTime($createDate2);

    return   $to_date->diff($dob)->format('%Y years, %m months, %d days');
}

/**
 * 
 * @param mixed $date1 
 * @param mixed $date2 
 * @return int a number
 */
function dateDifferenceInt($date1, $date2)
{
    $createDate1 = date_create($date1);
    $createDate2 = date_create($date2);
    $diff = date_diff($createDate1, $createDate2);
    return (int) $diff->format("%R%a");
}

function dateFormat($date): string
{
    $stringDate = strtotime($date);
    return date('l jS \of F Y', $stringDate);
}


function dateToString($date) : string 
{
    $datetime = DateTime::createFromFormat('d/m/Y', $date);
    return $datetime->format('F jS, Y');
}
