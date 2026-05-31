<?php
include('connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO messages (name, email, phone, message) 
            VALUES ('$name', '$email', '$phone', '$msg')";

    if ($conn->query($sql)) {
        echo "<script>alert('Message sent!'); window.location='index.html';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>