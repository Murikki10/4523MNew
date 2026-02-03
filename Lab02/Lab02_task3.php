<html>
<head>
    <title>Lab02_task3</title>
</head>
    <body>
        <?php
            $fib = array(1, 1);
            for($i = 1; $i < 20; $i++)
                $fib[] = $fib[$i] + $fib[$i-1];

            echo '<table border="1px" width="50%">';
            for($i = 0; $i < 20; $i++)
                echo '<tr><td>' . ($i+1) . '</td><td>' . $fib[$i] . '</td></tr>';
            echo '</table>';
        ?>
    </body>
</html>
