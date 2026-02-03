<html>

<head>
    <meta charset="utf-8" />
    <title>Lab03 Task 2 - Result</title>
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
        echo '<p><a href="Lab03_Task2.html">Back to form</a></p>';
        exit;
    }

    $num1_raw = isset($_POST['num1']) ? trim($_POST['num1']) : '';
    $num2_raw = isset($_POST['num2']) ? trim($_POST['num2']) : '';

    if ($num1_raw === '' || $num2_raw === '') {
        echo '<p>Both numbers are required.</p>';
        echo '<p><a href="Lab03_Task2.html">Back to form</a></p>';
        exit;
    }

    $num1 = filter_var($num1_raw, FILTER_VALIDATE_FLOAT);
    $num2 = filter_var($num2_raw, FILTER_VALIDATE_FLOAT);

    if ($num1 === false || $num2 === false) {
        echo '<p>Invalid number input. Please enter valid numbers.</p>';
        echo '<p><a href="Lab03_Task2.html">Back to form</a></p>';
        exit;
    }

    function calTwoNumbers($a, $b)
    {
        return $a + $b;
    }

    $total = calTwoNumbers($num1, $num2);

    function prettyNumber($n)
    {
        if (is_float($n) && intval($n) == $n) {
            return (string) intval($n);
        }
        return (string) $n;
    }

    $num1_display = prettyNumber($num1);
    $num2_display = prettyNumber($num2);
    $total_display = prettyNumber($total);

    echo "<h1>Calculate Two Numbers Function</h1>";
    echo "<p>The total of two numbers $num1_display and $num2_display is $total_display</p>";

    echo '<p><a href="Lab03_Task2.html">Back to form</a></p>';
    ?>
</body>

</html>