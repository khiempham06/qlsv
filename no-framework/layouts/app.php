<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quản lý Lớp học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Home</a>    
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="users.php">Người dùng</a></li>
                    <?php if(check()): ?>
                    <li class="nav-item"><a class="nav-link" href="users.php?action=edit&id=<?= $_SESSION['user_id'] ?>">Cài đặt tài khoản</a></li>                                       
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="assignments.php">Bài tập</a></li>
                    <li class="nav-item"><a class="nav-link" href="challenges.php">Câu đố</a></li>                                                         
                </ul>
                <div class="d-flex align-items-center">
                    <?php $u = user(); ?>
                    <span class="navbar-text me-3">Hi, <?= htmlspecialchars($u->name ?? $u->username) ?></span>                                                                
                    <form method="POST" action="login.php?action=logout" class="mb-0">
                        <?= csrf() ?>
                        <button class="btn btn-sm btn-danger">Đăng xuất</button>
                    </form>                                  
                </div>
            </div>
        </div>
    </nav>
    
    <?php if(isset($_SESSION['success'])): ?> 
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div> 
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>                                                                  
    
    <?php if(isset($_SESSION['error'])): ?> 
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?></div> 
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>                                                                       
    
    <!-- MOCK CONTENT -->
