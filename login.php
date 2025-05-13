<?php
// login.php
require_once 'db_config.php'; // Ensures session_start() and $mysqli are available

$username_or_email = "";
$password = "";
$errors = [];

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username_or_email = trim($_POST['username_or_email']);
    $password = $_POST['password'];

    if (empty($username_or_email)) {
        $errors[] = "Username or Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        // Check if input is email or username
        $field_type = filter_var($username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE $field_type = ?");
        $stmt->bind_param("s", $username_or_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect to home page or admin dashboard
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $errors[] = "Invalid username/email or password.";
            }
        } else {
            $errors[] = "Invalid username/email or password.";
        }
        $stmt->close();
    }
}

include 'header.php';
?>

<h2>Login</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form action="login.php" method="post">
    <div class="form-group">
        <label for="username_or_email">Username or Email:</label>
        <input type="text" name="username_or_email" id="username_or_email" value="<?php echo htmlspecialchars($username_or_email); ?>" required>
    </div>
    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit" class="btn">Login</button>
</form>
<p>Don't have an account? <a href="register.php">Register here</a>.</p>

<?php include 'footer.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
</body>
</html>
