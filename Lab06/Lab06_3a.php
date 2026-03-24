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
    $sql = "SELECT * FROM Customers";
    $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    echo '<table border= 1>
    <tr>
    <th>Action</th>
    <th>Cust ID</th>
    <th>Cust Name</th>
    <th>Cust Password</th>
    <th>Cust Gender</th>
    </tr>
    ';

    while ($rc = mysqli_fetch_assoc($rs)) {
        printf("<tr>");
        printf('<td><a href="Lab06_3a.php?custID=' . $rc['custID'] . '"> Update Record</a></td>');
        printf("<td>" . $rc['custID'] . "</td>");
        printf("<td>" . $rc['custName'] . "</td>");
        printf("<td>" . $rc['custPswd'] . "</td>");
        printf("<td>" . $rc['custGender'] . "</td>");
        printf("</tr>");
    }
    echo "</table>";
    $rs->close();


    if (isset($_GET['custID'])) {
        $custID = $_GET['custID'];
        $sql = "SELECT * FROM Customers WHERE custID=$custID";
        $rs = mysqli_query($conn, $sql) or die(mysqli_error($conn));
        $rc = mysqli_fetch_assoc($rs);

        $form =
            <<<EOD
        <form method = "post" action = "Lab06_3b.php">
        CustomerID <input type="text" value="%s" name="id" readonly/> <br>
        Customer Name <input type="text" value="%s" name="name" /> <br>
        Password <input type="password" value="%s" name="pw" /> <br>

        Gender
        <label><input type="radio" name="gender" value="M" %s/>Male </label>
        <label><input type="radio" name="gender" value="F" %s/>Female </label> <br>
        
        <input type="submit" value="Update Record">
        <input type="button" value="Cancel" onclick="window.location.href='Lab06_3a.php'" />
        </form>
        EOD;

        printf(
            $form,
            $rc['custID'],
            $rc['custName'],
            $rc['custPswd'],
($rc['custGender'] == "M" ? 'checked="checked"' : ''),
($rc['custGender'] == "F" ? 'checked="checked"' : '')
        );
    }

    ?>


</body>

</html>
