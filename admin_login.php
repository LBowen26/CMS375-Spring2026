<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Admin credentials
    $ADMIN_USER = "admin";
    $ADMIN_PASS = "admin123"; //self note: implement hashing

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user']      = $username;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login — Orlando Airport</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #e8edf2;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            padding: 40px 36px;
            width: 360px;
        }
        .login-card h1 {
            font-size: 1.4em;
            color: #003a6e;
            margin-bottom: 6px;
        }
        .login-card p.subtitle {
            color: #777;
            font-size: 0.88em;
            margin-bottom: 28px;
        }
        label {
            display: block;
            font-size: 0.85em;
            font-weight: bold;
            color: #444;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.95em;
            margin-bottom: 18px;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }
        button[type="submit"] {
            width: 100%;
            padding: 11px;
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover { background: #005fcc; }
        .error {
            background: #fff0f0;
            border: 1px solid #f5c6cb;
            color: #c0392b;
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 0.9em;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 18px;
            font-size: 0.85em;
            color: #007BFF;
            text-decoration: none;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="login-card">
    <h1> Admin Portal</h1>
    <p class="subtitle">Orlando Airport Flight Catalogue</p>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username"
               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
               placeholder="admin" autofocus required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <button type="submit">Sign In</button>
    </form>

    <a class="back-link" href="index.php">← Back to Flight Catalogue</a>
</div>
</body>
</html>
