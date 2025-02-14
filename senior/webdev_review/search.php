<?php

    // Enable displaying of errors for easier debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);

    // Include the database connection file
    include "db_connection.php";

    // Check if a name parameter is present in the GET request
    if(isset($_GET['name'])){
        // Extract the name from the GET parameter
        $name = $_GET['name'];
    }else{
        // Set an empty name if no parameter is provided
        $name = "";
    }

    // Establish a database connection
    $db = db_connect();
    //echo "SELECT * FROM items WHERE name = '".$name."' <br/>";
    //Prepare a secure SQL query 
    $query = $db->prepare("SELECT * FROM items WHERE name = '".$name."'");
    $query->execute();

    // Get the number of rows affected by the query
    $row_count = $query->rowCount();//Row count
   

?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EH | Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  </head>
  <body>

    <div class="container">
        <div class='row'>
            <div class="col-8 offset-md-2" style='margin-top:100px'>

                <h1>Search | Items</h1>

                <form method="get" >
                    <div class="input-group mb-3">
                        <input type="text" name='name' class="form-control" placeholder="Search ... ">
                        <div class="input-group-append">
                            <input class="btn btn-primary" type='submit' value='search' />
                        </div>
                    </div>
                </form>


                    <?php 
                        //check the if there are records returned by the query by checking row counts 
                        if(isset($row_count)){
                            if($row_count > 0){
                                ?>
                                <table class="table">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Item</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                <?php 
                                //loop through the rows and display the records
                                while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                            <tr>
                                            <th scope="row"><?php echo  $row['id']; ?></th>
                                            <td><?php echo  $row['name']; ?></td>
                                            <td><?php echo  $row['price']; ?></td>
                                            <td><?php echo  $row['quantity']; ?></td>
                                            </tr>
                                        
                                    <?php 
                                }
                                
                                ?> 
                                </tbody>
                                </table> 
                                <?php

                            }else{
                                echo "No items found!!";
                            }
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