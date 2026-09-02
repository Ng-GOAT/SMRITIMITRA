<?php
session_start();
include "../config/db.php";

if (isLoggedIn()) {
    header("Location: /SmritiMitra/index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = sanitize($_POST['role'] ?? 'patient');
    $age = intval($_POST['age'] ?? 0);
    $dob = sanitize($_POST['dob'] ?? '');
    $language = sanitize($_POST['language'] ?? 'English');

    if ($password !== $confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = 'Email already registered';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, age, dob, preferred_language) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssiss", $full_name, $email, $hashed, $role, $age, $dob, $language);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;

                $settings = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
                $settings->bind_param("i", $user_id);
                $settings->execute();

                $success = 'Account created! Redirecting to login...';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | SmritiMitra</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .auth-container { display:flex; justify-content:center; align-items:center; min-height:100vh; background:#f5f7fb; padding:20px; }
        .auth-card { background:white; border-radius:24px; padding:40px; width:100%; max-width:480px; box-shadow:0 10px 40px rgba(0,0,0,0.08); }
        .auth-logo { text-align:center; margin-bottom:30px; }
        .auth-logo .logo-icon { width:70px; height:70px; margin:0 auto 15px; display:flex; align-items:center; justify-content:center; font-size:36px; background:linear-gradient(135deg,#6d5dfc,#8b5cf6); border-radius:20px; }
        .auth-logo h1 { font-size:26px; margin-bottom:5px; }
        .auth-logo p { color:#64748b; font-size:14px; }
        .auth-form .form-row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .auth-form .form-group { margin-bottom:18px; }
        .auth-form label { display:block; font-size:14px; font-weight:600; margin-bottom:8px; color:#334155; }
        .auth-form input, .auth-form select { width:100%; padding:13px 16px; border:2px solid #e2e8f0; border-radius:12px; font-size:15px; outline:none; transition:0.2s; box-sizing:border-box; font-family:inherit; }
        .auth-form input:focus, .auth-form select:focus { border-color:#6d5dfc; }
        .auth-btn { width:100%; padding:15px; background:linear-gradient(135deg,#6d5dfc,#8b5cf6); color:white; border:none; border-radius:12px; font-size:16px; font-weight:700; cursor:pointer; transition:0.2s; }
        .auth-btn:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(109,93,252,0.3); }
        .auth-link { text-align:center; margin-top:20px; font-size:14px; color:#64748b; }
        .auth-link a { color:#6d5dfc; font-weight:600; text-decoration:none; }
        .auth-error { background:#fef2f2; color:#dc2626; padding:12px; border-radius:10px; font-size:14px; margin-bottom:20px; text-align:center; }
        .auth-success { background:#ecfdf5; color:#059669; padding:12px; border-radius:10px; font-size:14px; margin-bottom:20px; text-align:center; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="logo-icon">🧠</div>
                <h1>Create Account</h1>
                <p>Join SmritiMitra for cognitive care</p>
            </div>

            <?php if ($error): ?>
                <div class="auth-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="auth-success"><?php echo $success; ?>
                    <script>setTimeout(() => window.location.href='login.php', 2000);</script>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Min 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role">
                            <option value="patient">Patient</option>
                            <option value="caregiver">Caregiver</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" placeholder="Your age" min="1" max="120">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="dob">
                    </div>
                    <div class="form-group">
                        <label>Preferred Language</label>
                        <select name="language">
                            <option value="English">English</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Assamese">Assamese</option>
                            <option value="Bengali">Bengali</option>
                            <option value="Manipuri">Manipuri</option>
                            <option value="Mizo">Mizo</option>
                            <option value="Nagamese">Nagamese</option>
                            <option value="Khasi">Khasi</option>
                            <option value="Garo">Garo</option>
                            <option value="Bodo">Bodo</option>
                            <option value="Tripuri">Tripuri</option>
                            <option value="Marathi">Marathi</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="auth-btn">Create Account</button>
            </form>

            <div class="auth-link">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>
