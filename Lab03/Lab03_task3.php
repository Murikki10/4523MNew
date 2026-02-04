<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Lab03 Task 3 - Result</title>
    <style>
        body {
            font-family: serif;
            padding: 24px;
            max-width: 720px;
        }

        h1 {
            font-size: 24px;
        }

        p {
            font-size: 18px;
        }

        a {
            display: inline-block;
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo '<p>Please submit the form first.</p>';
        echo '<p><a href="Lab03_Task3.html">Back to form</a></p>';
        exit;
    }

    $first = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
    $last = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
    $yob_raw = isset($_POST['yob']) ? trim($_POST['yob']) : '';

    if ($first === '' || $last === '' || $yob_raw === '') {
        echo '<p>All fields are required.</p>';
        echo '<p><a href="Lab03_Task3.html">Back to form</a></p>';
        exit;
    }

    $yob = filter_var($yob_raw, FILTER_VALIDATE_INT);
    if ($yob === false || $yob < 1900 || $yob > intval(date('Y'))) {
        echo '<p>Invalid year of birth. Please enter a valid year.</p>';
        echo '<p><a href="Lab03_Task3.html">Back to form</a></p>';
        exit;
    }

    $first_safe = htmlspecialchars($first, ENT_QUOTES, 'UTF-8');
    $last_safe = htmlspecialchars($last, ENT_QUOTES, 'UTF-8');

    $currentYear = intval(date('Y'));
    $age = $currentYear - $yob;

    if ($age >= 18) {
        echo "<h1>Welcome! $first_safe $last_safe</h1>";
        echo "<p>You are now $age years old, so you can fill this form.</p>";
    } else {
        echo "<h1>Sorry: Your age is: $age.</h1>";
        echo "<p>You should be over 18 to fill this form</p>";
    }

    echo '<p><a href="Lab03_Task3.html">Back to form</a></p>';
    ?>
</body>

</html>
