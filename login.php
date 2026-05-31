<?php
// 1. DATABASE & ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Ensure this path is correct. If connect.php is in the same folder, use this:
if (file_exists('connect.php')) {
    include('connect.php');
} else {
    die("Error: 'connect.php' file not found. Please check your file path.");
}

// 2. LOGIN LOGIC
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $pass = $_POST['password'];

    // NOTE: Make sure your table name is 'site_users'
    $res = $conn->query("SELECT * FROM site_users WHERE email = '$email'");
    
    if ($res && $user = $res->fetch_assoc()) {
        // Check password using the hash
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username'];
          // Change this line to:
    header("Location: index.php?login=success"); 
    exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No user found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Kirtipur Tourism</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <link rel = "Stylesheet" href="auth.css">
</head>
<body>

    <div class="auth-card">
        <i class="fa-solid fa-circle-user" style="font-size: 3rem; color: #e67e22; margin-bottom: 15px;"></i>
        <h2>Sign In</h2>
        <p class="subtitle">Welcome back to Kirtipur Tourism</p>

        <?php if(isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
    <div class="input-group">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="email@example.com" required>
    </div>

    <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
    </div>

    <button type="submit" name="login">Login</button>
</form>

        <p class="footer-text">
            Don't have an account? <a href="register.php">Register</a>
        </p>
        
        <a href="index.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
    </div>

</body>
</html>