<?php

    // Enable displaying of errors for easier debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Include the database connection file
    include "db_connection.php";

    // Check if the registration form was submitted
    if(isset($_POST['submit'])){

        // Get user data from POST data
        $email = $_POST['email'];
        $name = $_POST['name'];
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];
        
        // Initialize an error message variable and validation flags
        $error_msg = "";
        $email_status = false;
        $password_status = false;
        $cpassword_status = false;


        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_status = true;
        }else{
            $error_msg = $error_msg . "Invalid Email format <br/>";
        }

        // Check if password and confirm password match
        if ($password == $cpassword) {
            $cpassword_status = true;
        }else{
            $error_msg = $error_msg . "Confirm password and password does not match<br/>";
        }

        // Validate password strength using regular expression
        $password_pattern = "/(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-])^.+$/";
        if (preg_match($password_pattern, $password)) {
            $password_status = true;
        } else {
            $error_msg .= "Password is weak. Please use a combination of uppercase, lowercase, numbers, and special characters.<br>";
        }
        echo $email_status. "|".$password_status. "|".$cpassword_status;

        // Proceed with registration if all validations pass
        // Check all the validations and default to deny
        if($email_status && $password_status && $cpassword_status){

            echo "Ddd";
            // Establish database connection
            $db = db_connect();
            // Prepare SQL query for user insertion
            $query = $db->prepare('INSERT INTO users (email, name, password,role) VALUES (?,?,?,?)');
            // Hash the password for security (assuming MD5 is sufficient for your use case)
            // Execute the query with user data
            $query->execute([$email,$name,hash('md5',$password),'user']);
            
            // Redirect to login page on successful registration
            header('Location:login.php');


        }
        

    }


?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EH | Sign up</title>
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
                        <input type="email" class="form-control" name="email" aria-describedby="emailHelp" placeholder="Enter email" value="aaa@gmail.com">
                        <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputEmail1">Full name</label>
                        <input type="text" class="form-control" name="name" placeholder="Name" value="aaa@gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Password" value="P@ssword1">
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Confirm password</label>
                        <input type="password" class="form-control" name="cpassword" placeholder="Confirm password" value="P@ssword1">
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="exampleCheck1">
                        <label class="form-check-label" for="exampleCheck1">Check me out</label>
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary" value='submit' >
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