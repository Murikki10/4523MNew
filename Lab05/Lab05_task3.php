<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Lab05 Task 3</title>
    <script>
    function clearForm() {
        var f = document.getElementById('addForm');
        if (!f) return;
        var inputs = f.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++) {
            var el = inputs[i];
            var t = el.type.toLowerCase();
            if (t === 'text' || t === 'password' || t === 'email' || t === 'number' || t === 'tel' || t === 'hidden') {
                el.value = '';
            } else if (t === 'radio' || t === 'checkbox') {
                el.checked = false;
            }
        }
        var selects = f.querySelectorAll('select');
        for (var j = 0; j < selects.length; j++) {
            selects[j].selectedIndex = -1;
        }
        var areas = f.querySelectorAll('textarea');
        for (var k = 0; k < areas.length; k++) {
            areas[k].value = '';
        }
        var msg = document.getElementById('msg');
        if (msg) msg.innerHTML = '';
    }
    </script>
</head>

<body>
    <h2>Add a new customer</h2>

    <?php
    require("conn.php");

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $custID = isset($_POST['custID']) ? trim($_POST['custID']) : '';
        $custName = isset($_POST['custName']) ? trim($_POST['custName']) : '';
        $custPswd = isset($_POST['custPswd']) ? trim($_POST['custPswd']) : '';
        $custGender = isset($_POST['custGender']) ? trim($_POST['custGender']) : '';

        if ($custID === '' || $custName === '' || $custPswd === '' || $custGender === '') {
            $message = 'Please fill in all fields.';
        } else {
            $chkSql = "SELECT 1 FROM Customers WHERE custID = ?";
            $chkStmt = mysqli_prepare($conn, $chkSql);
            mysqli_stmt_bind_param($chkStmt, 's', $custID);
            mysqli_stmt_execute($chkStmt);
            mysqli_stmt_store_result($chkStmt);

            if (mysqli_stmt_num_rows($chkStmt) > 0) {
                $message = 'Record already exist!';
                mysqli_stmt_close($chkStmt);
            } else {
                mysqli_stmt_close($chkStmt);

                $insSql = "INSERT INTO Customers (custID, custName, custPswd, custGender) VALUES (?, ?, ?, ?)";
                $insStmt = mysqli_prepare($conn, $insSql);
                if ($insStmt) {
                    mysqli_stmt_bind_param($insStmt, 'ssss', $custID, $custName, $custPswd, $custGender);
                    $ok = mysqli_stmt_execute($insStmt);
                    if ($ok) {
                        $message = 'A record is added successfully';
                        $custID = $custName = $custPswd = $custGender = '';
                    } else {
                        $message = 'Insert failed: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
                    }
                    mysqli_stmt_close($insStmt);
                } else {
                    $message = 'Prepare failed: ' . htmlspecialchars(mysqli_error($conn), ENT_QUOTES, 'UTF-8');
                }
            }
        }
    }

    if ($message !== '') {
        echo '<p id="msg"><strong>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</strong></p>';
    } else {
        echo '<p id="msg"></p>';
    }

    $valID = isset($custID) ? htmlspecialchars($custID, ENT_QUOTES, 'UTF-8') : '';
    $valName = isset($custName) ? htmlspecialchars($custName, ENT_QUOTES, 'UTF-8') : '';
    $valPswd = isset($custPswd) ? htmlspecialchars($custPswd, ENT_QUOTES, 'UTF-8') : '';
    $valGender = isset($custGender) ? htmlspecialchars($custGender, ENT_QUOTES, 'UTF-8') : '';
    ?>

    <form id="addForm" method="post" action="">
        <table>
            <tr>
                <td>Customer ID</td>
                <td><input type="text" name="custID" value="<?php echo $valID; ?>" /></td>
            </tr>
            <tr>
                <td>Customer Name</td>
                <td><input type="text" name="custName" value="<?php echo $valName; ?>" /></td>
            </tr>
            <tr>
                <td>Password</td>
                <td><input type="password" name="custPswd" value="<?php echo $valPswd; ?>" /></td>
            </tr>
            <tr>
                <td>Gender</td>
                <td>
                    <label><input type="radio" name="custGender" value="M" <?php echo ($valGender === 'M' ? 'checked' : ''); ?> /> Male</label>
                    <label><input type="radio" name="custGender" value="F" <?php echo ($valGender === 'F' ? 'checked' : ''); ?> /> Female</label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <input type="submit" value="Submit" />
                    <input type="button" value="Reset" onclick="clearForm()" />
                </td>
            </tr>
        </table>
    </form>

    <?php
    mysqli_close($conn);
    ?>

</body>

</html>