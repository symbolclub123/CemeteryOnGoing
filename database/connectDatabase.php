<?php
    // Database connection parameters
    $db_server = "localhost"; // Server where the database is hosted
    $db_user = "root"; // Database username
    $db_pass = ""; // Database password
    $db_name = "cemeteryproto"; // Database name

    // Establish a connection to the database
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

    // Check if the connection was successful
    if (!$conn) {
        // If connection failed, terminate the script and display an error message
        die("Connection failed: " . mysqli_connect_error());
    }

    // If connection is successful, display a success message
    //echo "You are connected <br>";
