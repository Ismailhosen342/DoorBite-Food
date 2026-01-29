 <?php
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            $error = "Invalid username or password.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DoorBite Food! - Login</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="login-container">
    <div class="login-logo">DB</div>
    <h1 class="login-title">DoorBite Food!</h1>
    

    <div class="login-box">
      <h2>Login</h2>
      

      <?php if ($error): ?>
        <div style="color:#ef4444;margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="e.g. admin" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">Sign In</button>
      </form>

      <p style="margin-top:15px;text-align:center;">
        Don’t have an account?
        <a href="signup.php">Sign up</a>
      </p>
    </div>

    <p style="text-align:center;margin-top:25px;color:#6b7280;font-size:13px;">
      © <?php echo date("Y"); ?> All Rights Reserved by Ismail
    </p>
  </div>
</body>
</html>
