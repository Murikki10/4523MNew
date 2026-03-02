<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Lab05 Task 2</title>
</head>

<body>
    <?php
    require("conn.php");

    $sql = "SELECT * FROM Customers";
    $result = mysqli_query($conn, $sql) or die(mysqli_error($conn));

    // Start table using your original style
    echo '<table border="1px">
        <tr><td>Cust ID</td><td>Cust Name</td><td>Cust Password</td><td>Cust Gender</td></tr>';

    while ($rc = mysqli_fetch_assoc($result)) {
        // Keep your original printf style but output readonly form controls for name/password/gender
        $cid = htmlspecialchars($rc['custID'], ENT_QUOTES, 'UTF-8');
        $cname = htmlspecialchars($rc['custName'], ENT_QUOTES, 'UTF-8');
        $cpswd = htmlspecialchars($rc['custPswd'], ENT_QUOTES, 'UTF-8');
        $cgender = isset($rc['custGender']) ? $rc['custGender'] : '';

        // Determine checked state for radio buttons; accept 'M'/'F' or 'Male'/'Female'
        $maleChecked = ($cgender === 'M' || strcasecmp($cgender, 'male') === 0) ? 'checked' : '';
        $femaleChecked = ($cgender === 'F' || strcasecmp($cgender, 'female') === 0) ? 'checked' : '';

        // Use array-style names but make inputs readonly; radio buttons disabled so they cannot be changed
        printf(
            "<tr>
                <td>%s</td>
                <td><input type='text' name='custName[%s]' value='%s' readonly /></td>
                <td><input type='text' name='custPswd[%s]' value='%s' readonly /></td>
                <td>
                    <label><input type='radio' name='custGender[%s]' value='M' %s disabled /> Male</label>
                    <label><input type='radio' name='custGender[%s]' value='F' %s disabled /> Female</label>
                </td>
            </tr>",
            $cid,
            $cid, $cname,
            $cid, $cpswd,
            $cid, $maleChecked,
            $cid, $femaleChecked
        );
    }

    echo '</table>';

    mysqli_free_result($result);
    mysqli_close($conn);
    ?>
</body>

</html>