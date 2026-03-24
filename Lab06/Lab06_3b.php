<!DOCTYPE html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 6 Task 3</title>
</head>

<body>

    <?php
    include("conn.php");
    extract($_POST);
    $sql = "UPDATE Customers SET custName='$name', custPswd='$pw', custGender='$gender' WHERE custID='$id'";
    mysqli_query($conn, $sql) or die(mysqli_error($conn));

    if (mysqli_affected_rows($conn) > 0) {
        header("location:Lab06_3a.php");
    } else {
        echo "Update unsuccessfully";
    }

    ?>


</body>

</html>
