<?php
    require_once ("Lab02_1.php");
    $sql = "SELECT * FROM students";
    $rs = mysqli_query($conn, $sql)
            or die (mysqli_error(($conn)));
    while($rc = mysqli_fetch_assoc($rs)){
        $student[] = $rc;
    }        

    echo json_encode($student);

?>