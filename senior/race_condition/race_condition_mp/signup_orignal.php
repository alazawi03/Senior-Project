<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    include "db_connection.php";


    if(isset($_POST['submit'])){
        $email = $_POST['email'];
        $name = $_POST['name'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        

        if(strlen($email) > 255){
            $email = substr($email, 0, 255);
        }

        $db = db_connect();
        $query = $db->prepare('INSERT INTO `users` (`email`, `name`, `password`,`role`) VALUES (?,?,?,?)');
        $query->execute([$email,$name,hash('md5',$password),'']);
        
        $query = $db->prepare("SELECT * FROM users WHERE email = ?");
        $query->execute([$email]);
        $row_count = $query->rowCount();//Row count
        if($row_count > 0){
          $row = $query->fetch(PDO::FETCH_ASSOC);

          if($_POST['email'] == $row['email'])
          {
            $query = $db->prepare("UPDATE users set role='user' WHERE email = ?");
            $query->execute([$email]);
          }
        }


        header('Location:login.php');
    
        

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

                <form method="post" action="signup.php">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Email address</label>
                        <input type="text" class="form-control" name="email" aria-describedby="emailHelp" placeholder="Enter email">
                        <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Full name</label>
                        <input type="text" class="form-control" name="name" placeholder="Name">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Password">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Confirm password</label>
                        <input type="password" class="form-control" name="cpassword" placeholder="Confirm password">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1">
                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
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