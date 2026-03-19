<?php
require 'config.php';
if (!check()) { header('Location: login.php'); exit; }
$u = user();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>Xin chào, <?= htmlspecialchars($u->name) ?> (<?= htmlspecialchars($u->role) ?>)</h2> 
    <form method="POST" action="login.php?action=logout" class="d-inline">
        <?= csrf() ?>
        <button class="btn btn-danger">Đăng xuất</button>
    </form>                                             
    <hr>
    <a href="users.php" class="btn btn-info">Quản lý người dùng</a>                                                                          
    <a href="assignments.php" class="btn btn-primary">Bài tập</a>                                                                               
    <a href="challenges.php" class="btn btn-warning">Giải đố</a>                                                                          
</body>
</html>