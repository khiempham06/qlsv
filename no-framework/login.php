<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user->password)) {
        $_SESSION['user_id'] = $user->id;
        header('Location: dashboard.php');
        exit;
    }
    $_SESSION['error'] = 'Sai thông tin đăng nhập';
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center">Đăng nhập</h3>
            <?php if(isset($_SESSION['error'])): ?> 
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div> 
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>                                                                       
            <form method="POST" action="login.php">
                <?= csrf() ?>
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>                                                
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>                                        
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>                                                                                   
            </form>
        </div>
    </div>
</body>
</html>