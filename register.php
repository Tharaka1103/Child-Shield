<?php
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        die("Passwords do not match");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: login.php?registration=success");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Shield - Register</title>
    <link rel="stylesheet" href="css/register.css">
    <script src="https://kit.fontawesome.com/20f08145b4.js" crossorigin="anonymous"></script></head>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>Join with Child Shield Today!</h1>
            <p>Join our community of caring parents and guardians.</p>
            <img src="images/register.jpg" alt="Happy baby">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
        <div class="right-section">
            <h1>Register</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="text" name="fullname" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Register</button>
            </form>
            <div class="social-login">
                <button class="apple"><i class="fab fa-apple"></i> Register with Apple</button>
                <button class="google"><i class="fab fa-google"></i> Register with Google</button>
                <button class="facebook"><i class="fab fa-facebook-f"></i> Register with Facebook</button>
            </div>
        </div>
    </div>
</body>
</html>
