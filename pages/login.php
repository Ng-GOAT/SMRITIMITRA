<?php
session_start();
include "../config/db.php";

if (isLoggedIn()) {
    header("Location: /SmritiMitra/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        header("Location: /SmritiMitra/index.php");
        exit;
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-container { display:flex; justify-content:center; align-items:center; min-height:100vh; background:#f5f7fb; padding:20px; }
        .auth-card { background:white; border-radius:24px; padding:40px; width:100%; max-width:420px; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
        .auth-logo { text-align:center; margin-bottom:30px; }
        .auth-logo .logo-icon { width:70px; height:70px; margin:0 auto 15px; display:flex; align-items:center; justify-content:center; font-size:36px; background:linear-gradient(135deg,#6d5dfc,#8b5cf6); border-radius:20px; }
        .auth-logo h1 { font-size:26px; margin-bottom:5px; }
        .auth-logo p { color:#64748b; font-size:14px; }
        .auth-form .form-group { margin-bottom:20px; }
        .auth-form label { display:block; font-size:14px; font-weight:600; margin-bottom:8px; color:#334155; }
        .auth-form input { width:100%; padding:14px 16px; border:2px solid #e2e8f0; border-radius:12px; font-size:15px; outline:none; transition:0.2s; box-sizing:border-box; }
        .auth-form input:focus { border-color:#6d5dfc; }
        .auth-btn { width:100%; padding:15px; background:linear-gradient(135deg,#6d5dfc,#8b5cf6); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer; transition:0.2s; }
        .auth-btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(109,93,252,0.3); }
        .auth-link { text-align:center; margin-top:20px; font-size:14px; color:#64748b; }
        .auth-link a { color:#6d5dfc; font-weight:600; text-decoration:none; }
        .auth-error { background:#fef2f2; color:#dc2626; padding:12px; border-radius:10px; font-size:14px; margin-bottom:20px; text-align:center; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🧠</div>
                <h1>SmritiMitra</h1>
                <p>AI Cognitive Care Companion</p>
            </div>

            <?php if ($error): ?>
                <div class="auth-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="auth-btn">Sign In</button>
            </form>

            <div class="auth-link">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </div>
</body>
</html>
