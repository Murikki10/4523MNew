<?php
$hostname="127.0.0.1";
$username= "root";
$pwd = "";
$db= "lab05";
    $conn=mysqli_connect("$hostname","$username","$pwd","$db") or die("Could not connect to database");
?>