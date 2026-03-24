<!DOCTYPE html>
<html>
<head>
    <title>Lab06 Task 2b</title>
</head>
<body>
    <?php
        require("conn.php");

        $sql = "Delete FROM Customers WHERE custID = ". $_POST['cID'];
        $result=mysqli_query($conn,$sql) or die(mysqli_error($conn));
        if(mysqli_affected_rows($conn) > 0){
            header('Location: Lab06_2a.php');
        }
        else {
            echo'Delete failed!';
        }
?>
</body>
</html>