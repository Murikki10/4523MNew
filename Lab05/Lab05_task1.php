<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Lab05 Task 1</title>
</head>

<body>
    <?php
    require("conn.php");

    $sql = "SELECT * FROM Customers";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    echo '<table border="1px">
        <tr><td>Cust ID</td><td>Cust Name</td><td>Cust Password</td><td>Cust Gender</td></tr>';
    while ($rc = mysqli_fetch_assoc($result)) {
        // Keep your original printf style but without the delete link, and escape output
        printf(
            "<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
            htmlspecialchars($rc['custID'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($rc['custName'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($rc['custPswd'], ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($rc['custGender'], ENT_QUOTES, 'UTF-8')
        );
    }
    echo '</table>';

    mysqli_free_result($result);
    mysqli_close($conn);
    ?>
</body>

</html>