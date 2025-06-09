<?php
// register.php
require_once 'config.php';

$username = $password = $confirm_password = '';
$username_err = $password_err = $confirm_password_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($_POST['username']))) {
        $username_err = 'Masukkan username.';
    } else {
        $sql = "SELECT id FROM users WHERE username = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('s', $param_username);
            $param_username = trim($_POST['username']);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows === 1) {
                    $username_err = 'Username ini sudah digunakan.';
                } else {
                    $username = trim($_POST['username']);
                }
            } else {
                echo "Ada yang salah. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    }

    if (empty(trim($_POST['password']))) {
        $password_err = 'Masukkan password.';
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = 'Password harus memiliki setidaknya 6 karakter.';
    } else {
        $password = trim($_POST['password']);
    }

    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = 'Konfirmasi password.';
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = 'Password tidak cocok.';
        }
    }

    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'user')"; // Default role 'user'
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param('ss', $param_username, $param_password);
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            if ($stmt->execute()) {
                header('Location: index.php');
                exit;
            } else {
                echo "Ada yang salah. Silakan coba lagi nanti.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - FootballGear</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Daftar Akun FootballGear</h1>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <div>
                <label>Username</label>
                <input type="text" name="username" value="<?php echo $username; ?>">
                <span class="error"><?php echo $username_err; ?></span>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password">
                <span class="error"><?php echo $password_err; ?></span>
            </div>
            <div>
                <label>Konfirmasi Password</label>
                <input type="password" name="confirm_password">
                <span class="error"><?php echo $confirm_password_err; ?></span>
            </div>
            <div>
                <input type="submit" value="Daftar">
            </div>
            <p>Sudah punya akun? <a href="index.php">Login di sini</a>.</p>
        </form>
    </div>
</body>
</html>