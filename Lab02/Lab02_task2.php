<html>
<head>
    <title>Lab 2 Task 2</title>
</head>
<body>
    <?php
        $mathMark = array(70, 40, 60, 50, 20, 30, 10, 100, 80, 90);

        $index = 0;
        do{
            echo $mathMark[$index++] . " ";
        } while($index < count($mathMark));

        $max = $mathMark[0];
        $index =1;
        while($index < count($mathMark)){
            if($mathMark[$index] > $max)
            $max = $mathMark[$index];
            $index++;
        }
        echo "<br />The maximum mark is".$max;

        $index = 0;
        $min = $mathMark[0];
        for($i =0; $i < count($mathMark); $i++){
            if($min > $mathMark[$index])
            $min = $mathMark[$index];
            $index++;
        }
        echo "<br />The minimum mark is" .$min;
        ?>
</body>
</html>