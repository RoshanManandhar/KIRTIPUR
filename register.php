<?php
include('connect.php');
if (isset($_POST['register'])) {
    // 1. CAPTURE the data from the form first!
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];
    
    // 2. HASH the password
    $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

    // 3. NOW run the query (Using 'name' to match your database)
    $sql = "INSERT INTO site_users (username, email, password) VALUES ('$name', '$email', '$hashed_pass')";
    
    try {
        if ($conn->query($sql)) {
            header("Location: login.php?msg=Registration Successful");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            $error = "This email is already registered!";
        } else {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register | Kirtipur</title>
 <link rel = "Stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-card">
        <h2>Register</h2>
        <form method="POST" action="register.php">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="register">Register</button>
</form>
        <p style="margin-top:15px;">Already have an account? <a href="login.php">Sign In</a></p>
    </div>
</body>
</html>