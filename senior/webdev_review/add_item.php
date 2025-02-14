<?php

    // Enable displaying of errors for easier debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Include the database connection file
    include "db_connection.php";

    // Check if the form was submitted
    if(isset($_POST['submit'])){

        // Get item data from POST request
        $name = $_POST['name'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        
        // Initialize validation status and error message
        $status = false;
        $error_msg = "";

        // Validate price and quantity as numeric values
        if (is_numeric($price) && is_numeric($quantity)) {
            $status = true;
        }else{
            $error_msg = $error_msg . "Invalid item data<br/>";
        }

        //Check all validation and default to deny
        // Proceed with insertion if validation passes
        if($status){

            // Establish database connection
            $db = db_connect();
            // Prepare SQL query and excute it for item insertion
            $query = $db->prepare('INSERT INTO `items` (`name`, `price`, `quantity`) VALUES (?,?,?)');
            $query->execute([$name,$price,$quantity]);

             // Update error message to reflect successful insertion
            $error_msg = "Item inserted successfully!";

        }
        
    }
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EH | Add item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  </head>
  <body>

    <div class="container">
        <div class='row'>
            <div class="col-4 offset-md-4" style='margin-top:100px'>

                <h1>Item | Add new Item</h1>

                <form method="post" action="add_item.php">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" placeholder="Name">
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="text" class="form-control" name="price" placeholder="price">
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="text" class="form-control" name="quantity" placeholder="quantity">
                    </div>
                    <br/>
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