<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    include "db_connection.php";


    if(isset($_POST['submit'])){
        $email = $_POST['email'];
        $code = $_POST['code'];

        $db = db_connect();

        //check if the code exist and has not been used
        $query = $db->prepare("SELECT * FROM codes WHERE code = ? AND used = 0");
        $query->execute([$code]);

        $row_count = $query->rowCount();
        

        if($row_count > 0){
            //get the amount
            $row = $query->fetch(PDO::FETCH_ASSOC);
            $gift_amount = $row['amount'];

            //add the amount to the user
            $query = $db->prepare('UPDATE `users` set amount=amount+? where email=?');
            $query->execute([$gift_amount,$email]);
            
            //expire the code
            $query = $db->prepare("UPDATE codes set used=1 WHERE code = ?");
            $query->execute([$code]);

            $error_msg = "The code used successfully!";
        }else{
            $error_msg = "Invalid code or the code has been already used!";
        }        

    }


?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  </head>
  <body>

    <div class="container">
        <div class='row'>
            <div class="col-4 offset-md-4" style='margin-top:100px'>

                <h1>Sign up</h1>

                <form method="post" action="redeem_code.php">
                    
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email </label>
                        <input type="text" class="form-control" name="email"  placeholder="e.g. alice@gmail.com">
                    </div>

                    <div class="form-group">
                        <label for="exampleInputEmail1">Enter code </label>
                        <input type="text" class="form-control" name="code"  placeholder="XXX-XXX">
                    </div>
                   
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                    </form>

                    <div style='color:red;margin-top:20px;'>
                        <?php 
                            if(isset($error_msg))
                                echo $error_msg; 
                        ?>
                    </div>
            </div>
        </div>
     </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  </body>
</html>