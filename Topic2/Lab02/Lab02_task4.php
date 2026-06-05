<!DOCTYPE html>
<html>
<head>
   <title>Lab02 Challenge Task</title>
   <link href="css/style.css" type="text/css" rel="stylesheet"/>
    <script src="jquery/jquery-2.1.4.js"></script>
   <script>
     $(document).ready(function(){
        $('#btnResult').click(function(){
            var studName = $("#name").val();
            var studPass = $("#password").val();
            var studResult = 0;
            var validStudent = false;
            
            $.ajax({
                url: "Lab02_task1.php",
                dataType: "json",
                success: function(result){
                    for(var i = 0; i < result.length; i++) {
                        if(result[i].sname === studName && result[i].spassword === studPass) {
                            validStudent = true;
                            studResult = result[i].sresult;
                        }
                    }
                    if(validStudent) {
                        alert("Hello! " + studName + ", your result is: " + studResult);                    } else {
                        alert("Invalid name or password");
                    }
                },
                error: function (err) {
                    console.log("error" + err);
                }
            });
        });
     });
   </script>
</head>
<body>
    <table border="1">    
        <tr>
            <td> Name : </td>
            <td><input type="text" id="name" name="name"></td>
        </tr>
        <tr>
            <td> Password : </td>
            <td><input type="password" id="password" name="password"></td>
        </tr>
        <tr>
            <td></td>
            <td><input type="button" id="btnResult" name="Get Result" value="Get Result"></td>
        </tr>        
    </table>
</body>
</html>