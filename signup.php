<!--<?php-->
<!--require_once 'config/database.php';-->

<!--if (isLoggedIn()) {-->
<!--    header('Location: dashboard.php');-->
<!--    exit();-->
<!--}-->

<!--$error = '';-->
<!--$success = '';-->

<!--if ($_SERVER['REQUEST_METHOD'] === 'POST') {-->
<!--    $username = trim($_POST['username'] ?? '');-->
<!--    $password = $_POST['password'] ?? '';-->
<!--    $confirm  = $_POST['confirm_password'] ?? '';-->
<!--    $role     = $_POST['role'] ?? 'Staff';-->

<!--    if ($username === '' || $password === '' || $confirm === '') {-->
<!--        $error = "All fields are required.";-->
<!--    } elseif (strlen($password) < 6) {-->
<!--        $error = "Password must be at least 6 characters.";-->
<!--    } elseif ($password !== $confirm) {-->
<!--        $error = "Passwords do not match.";-->
<!--    } else {-->
        // check if username exists
<!--        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");-->
<!--        $stmt->execute([$username]);-->

<!--        if ($stmt->fetch()) {-->
<!--            $error = "Username already exists. Please choose another.";-->
<!--        } else {-->
<!--            $hash = password_hash($password, PASSWORD_DEFAULT);-->

<!--            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");-->
<!--            $stmt->execute([$username, $hash, $role]);-->

<!--            $success = "Account created successfully. You can login now!";-->
<!--        }-->
<!--    }-->
<!--}-->
<!--?>-->
<!--<!DOCTYPE html>-->
<!--<html lang="en">-->
<!--<head>-->
<!--  <meta charset="UTF-8">-->
<!--  <meta name="viewport" content="width=device-width, initial-scale=1.0">-->
<!--  <title>DoorBite Food! - Sign Up</title>-->
<!--  <link rel="stylesheet" href="css/style.css">-->
<!--</head>-->
<!--<body>-->
<!--  <div class="login-container">-->
<!--    <div class="login-logo">DB</div>-->
<!--    <h1 class="login-title">DoorBite Food!</h1>-->
<!--    <p class="login-subtitle">Create your account</p>-->

<!--    <div class="login-box">-->
<!--      <h2>Sign Up</h2>-->
<!--      <p>Create a new account to access the system.</p>-->

<!--      <?php if ($error): ?>-->
<!--        <div style="color:#ef4444;margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></div>-->
<!--      <?php endif; ?>-->

<!--      <?php if ($success): ?>-->
<!--        <div style="color:#10b981;margin-bottom:15px;"><?php echo htmlspecialchars($success); ?></div>-->
<!--      <?php endif; ?>-->

<!--      <form method="POST">-->
<!--        <div class="form-group">-->
<!--          <label for="username">Username</label>-->
<!--          <input type="text" id="username" name="username" placeholder="e.g. admin" required>-->
<!--        </div>-->

<!--        <div class="form-group">-->
<!--          <label for="password">Password</label>-->
<!--          <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>-->
<!--        </div>-->

<!--        <div class="form-group">-->
<!--          <label for="confirm_password">Confirm Password</label>-->
<!--          <input type="password" id="confirm_password" name="confirm_password" required>-->
<!--        </div>-->

<!--        <div class="form-group">-->
<!--          <label for="role">Role</label>-->
<!--          <select id="role" name="role">-->
<!--            <option value="Admin">Admin</option>-->
<!--            <option value="Staff" selected>Staff</option>-->
<!--          </select>-->
<!--        </div>-->

<!--        <button type="submit" class="btn btn-primary">Create Account</button>-->
<!--      </form>-->

<!--      <p style="margin-top:15px;text-align:center;">-->
<!--        Already have an account?-->
<!--        <a href="login.php">Login</a>-->
<!--      </p>-->
<!--    </div>-->

<!--    <p style="text-align:center;margin-top:25px;color:#6b7280;font-size:13px;">-->
<!--      © <?php echo date("Y"); ?> All Rights Reserved by Ismail-->
<!--    </p>-->
<!--  </div>-->
<!--</body>-->
<!--</html>-->
