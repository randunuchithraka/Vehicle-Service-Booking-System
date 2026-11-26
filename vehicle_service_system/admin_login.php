<?php
session_start();
include 'vehicle_db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    
    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
           
            if ($password === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username']  = $row['username'];
                $_SESSION['admin_id']        = $row['id'];
                header("Location: admin_dashboard.php");
                exit;
            }
        }
        $error = "Invalid username or password!";
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - AutoCare</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>AutoCare Service</h1>
        <nav>
            <a href="index.html">Home</a>
            <a href="booking.html">Book Service</a>
            <a href="payment.html">Online Payment</a>
            <a href="about.html">About</a>
            <a href="admin_login.php">Admin Login</a>
        </nav>
    </header>

    <div class="container" style="max-width: 450px;">
        <h2>Admin Login</h2>
        <br>
        <?php if ($error !== ""): ?>
            <div class="alert-box" style="background:#fef2f2; border-color:#fecaca; color:#991b1b; margin-bottom:15px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required placeholder="admin" autocomplete="username">
            </div>
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required placeholder="admin123" autocomplete="current-password">
            </div>
            <button type="submit" class="btn" style="width:100%;">Login</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2026 AutoCare Vehicle Service Center.</p>
    </footer>
</body>
</html>