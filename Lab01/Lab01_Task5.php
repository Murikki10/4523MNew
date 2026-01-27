<html>
    <head>
        <title>Lab 01 - Task 5</title>
    </head>
    <body>
        <?php
    $day = date("j");
    $month = date("n");
    $year = date("Y");

    $monthName = date("F", strtotime("$year-$month-$day"));

    $isLeap = false;
    if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
        $isLeap = true;
    }

    if ($month == 1) { 
        $daysInMonth = 31;
    } elseif ($month == 2) { 
        $daysInMonth = $isLeap ? 29 : 28;
    } elseif ($month == 3) { 
        $daysInMonth = 31;
    } elseif ($month == 4) {
        $daysInMonth = 30;
    } elseif ($month == 5) {
        $daysInMonth = 31;
    } elseif ($month == 6) {
        $daysInMonth = 30;
    } elseif ($month == 7) {
        $daysInMonth = 31;
    } elseif ($month == 8) {
        $daysInMonth = 31;
    } elseif ($month == 9) {
        $daysInMonth = 30;
    } elseif ($month == 10) {
        $daysInMonth = 31;
    } elseif ($month == 11) {
        $daysInMonth = 30;
    } elseif ($month == 12) {
        $daysInMonth = 31;
    } else {
        $daysInMonth = 0;
    }

    echo "Today is $day $monthName $year<br>";
    echo "$monthName has $daysInMonth days.<br>";
    ?>
    </body>
</html>