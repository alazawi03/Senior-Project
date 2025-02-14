<?php

    // Enable displaying of errors for easier debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Include the database connection file
    include "db_connection.php";

    // Start a session to access session data
    session_start();
    
    // Check if a user is logged in (has an ID in the session)
    if(isset($_SESSION['id'])){

        // Get the user ID from the session
        $id = $_SESSION['id'];
        
        // Establish a database connection
        $db = db_connect();

        // Prepare a SQL query to fetch user details based on ID
        $query = $db->prepare("SELECT * FROM users WHERE id = ?");
        $query->execute([$id]);

        // Check if any rows were found (valid user)
        $row_count = $query->rowCount();//Row count
        if($row_count > 0){
            // Fetch the user data
            $row = $query->fetch(PDO::FETCH_ASSOC);
            
            // Extract user information from the fetched data
            $id = $row['id'];
            $name = $row['name'];
            $email = $row['email'];
            $role = $row['role'];

        }

        
    }else{

        // Set an error message for unauthorized access
        $error_msg = "Invalid page, please go to login page.";
    }


?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EH | Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  </head>
  <body>

    <div class="container">
        <div class='row'>
            <div class="col-4 offset-md-4" style='margin-top:100px'>

                <h1>Profile</h1>

                <div>
                    <?php 
                        if(isset($name)){
                            echo "Name : $name <br/>    Email : $email <br/> Role : $role <br/>";
                        }
                    ?>
                </div>

                <div style='color:red;margin-top:20px;'>
                    <?php 
                        if(isset($error_msg)){
                            echo $error_msg; 
                        }
                    ?>
                </div>
            </div>
        </div>
     </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  </body>
</html>