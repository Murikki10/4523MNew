<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Lab03 Task 1 - Result</title>
    <style>
        body {
            font-family: serif;
            padding: 24px;
            max-width: 720px;
        }

        p {
            font-size: 18px;
        }

        a {
            display: inline-block;
            margin-top: 16px;
        }
    </style>
</head>

<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo "<p>Please submit the form first.</p>";
        echo '<p><a href="Lab03_Task1.html">Back to form</a></p>';
        exit;
    }

    $name = isset($_POST['name']) && trim($_POST['name']) !== '' ? htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8') : 'Unknown';
    $gradyear = isset($_POST['gradyear']) && trim($_POST['gradyear']) !== '' ? htmlspecialchars(trim($_POST['gradyear']), ENT_QUOTES, 'UTF-8') : 'Unknown year';
    $alumni = isset($_POST['alumni']) ? $_POST['alumni'] : 'no';
    $comments = isset($_POST['comments']) && trim($_POST['comments']) !== '' ? htmlspecialchars(trim($_POST['comments']), ENT_QUOTES, 'UTF-8') : 'No comment';

    if ($alumni === 'yes') {
        $alumniText = 'is an alumni';
    } else {
        $alumniText = 'is not an alumni';
    }

    echo "<p>$name graduate in $gradyear $alumniText with comment: $comments</p>";

    echo '<p><a href="Lab03_Task1.html">Back to form</a></p>';
    ?>
</body>

</html>