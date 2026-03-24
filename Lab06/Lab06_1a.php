<!DOCTYPE html>
<html>
<head>
    <title>Lab06 Task 1a</title>
</head>
<body>
<?php
    require("conn.php");

    $sql="SELECT * FROM Customers";
    $result=mysqli_query($conn,$sql) or die(mysqli_error($conn));

    echo'<table border="1px">
        <tr><td>Action</td><td>Cust ID</td><td>Cust Name</td><td>Cust Password</td><td>Cust Gender</td></tr>';
    while($rc=mysqli_fetch_assoc($result)){
        printf("<tr><td><a href='Lab06_1b.php?custID=%s'>Delete Record</a> 
        </td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", $rc['custID'], $rc['custID'], $rc['custName'], $rc['custPswd'], $rc['custGender']);
    }
    echo'</table>';

    mysqli_free_result($result);
    mysqli_close($conn);
?>
</body>
</html>