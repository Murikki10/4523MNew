<html>
    <head>
        <title>Lab 01 - Task 4</title>
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

    switch ($month) {
        case 1:
        case 3:
        case 5:
        case 7:
        case 8:
        case 10: 
        case 12: 
            $daysInMonth = 31;
            break;
        case 4:
        case 6:
        case 9:
        case 11:
            $daysInMonth = 30;
            break;
        case 2: 
            $daysInMonth = $isLeap ? 29 : 28;
            break;
        default:
            $daysInMonth = 0; 
    }

    echo "Today is $day $monthName $year<br>";
    echo "$monthName has $daysInMonth days.<br>";
    ?>
    </body>
</html>