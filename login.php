<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, fullname, email, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['fullname'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Child Shield - Login</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://kit.fontawesome.com/20f08145b4.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <div class="left-section">
            <h1>Welcome back to Child Shield</h1>
            <p>Protecting and nurturing your little ones with love and care.</p>
            <img src="images/login.jpg" alt="Happy baby">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
        <div class="right-section">
            <h1>Login</h1>
            <?php
            if (isset($error)) {
                echo "<p class='error'>$error</p>";
            }
            ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="social-login">
                <button class="apple"><i class="fab fa-apple"></i> Login with Apple</button>
                <button class="google"><i class="fab fa-google"></i> Login with Google</button>
                <button class="facebook"><i class="fab fa-facebook-f"></i> Login with Facebook</button>
            </div>
        </div>
    </div>
</body>
</html>
