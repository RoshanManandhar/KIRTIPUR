<?php
// Ensure this path points correctly to your connect.php
include(__DIR__ . '/../connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 // Now $conn will be defined because connect.php was found
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // 2. Insert into Database
    $sql = "INSERT INTO messages (name, email, phone, message, status) 
              VALUES ('$name', '$email', '$phone', '$message', 'Pending')";

    if ($conn->query($sql)) {
        // Success: Redirect back with a alert
     echo "<script>
            alert('Thank you! Message sent successfully.');
            window.location.href='../index.php#contact'; 
          </script>";
    } else {
        // Error: Show the specific database error
        echo "Database Error: " . $conn->error;
    }
} else {
    // If someone tries to access this file directly
    header("Location: ../index.php");
}
?>