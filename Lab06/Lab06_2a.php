<!DOCTYPE html>
<html>
<head>
    <title>Lab06 Task 2a</title>
</head>
<body>
    <script>
        function setValue(custID){
            document.getElementById("cID").value=custID;
            document.forms[0].submit();
        }
        </script>
    <?php
    require("conn.php");

    $sql="SELECT * FROM Customers";
    $result=mysqli_query($conn,$sql) or die(mysqli_error($conn));

    echo'<form method="post" action="Lab06_2b.php"><table border="1px">
        <tr><td>Action</td><td>Cust ID</td><td>Cust Name</td><td>Cust Password</td><td>Cust Gender</td></tr>';
    while($rc=mysqli_fetch_assoc($result)){
        printf("<tr><td><input type='button' onclick='setValue(%s)' value='Delete'/></td>
        <td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>", $rc['custID'], $rc['custID'], $rc['custName'], $rc['custPswd'], $rc['custGender']);
    }
    echo'</table><input type="hidden" value"" id="cID" name="cID"/></from>';

    mysqli_free_result($result);
    mysqli_close($conn);

    ?>
</body>
</html>