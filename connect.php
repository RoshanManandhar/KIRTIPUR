<?php
// Database connection settings
$servername = "localhost:3306";   // Usually 'localhost' in XAMPP
$username   = "root";        // Default XAMPP username
$password   = "";            // Default XAMPP password is empty
$dbname     = "kirtipur_db"; // Your database name

// Create connection
try{
$conn = new mysqli("localhost:3306", "root", "", "kirtipur_db");
$conn = new mysqli($servername, $username, $password, $dbname);

}
catch(mysqli_sql_exception){
    echo("Could not connect !");
}

// Check connection
if ($conn->connect_error) {
    //echo "Connected failed";
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully1"; // (Uncomment for testing)
?>