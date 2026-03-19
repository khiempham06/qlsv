<?php
require 'config.php';
if (!check()) { header('Location: login.php'); exit; }
$u = user();

$action = $_GET['action'] ?? 'index';

if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($u->role === 'teacher') {
        $title = $_POST['title'];
        $fileObj = $_FILES['file'] ?? null;
        if ($fileObj && $fileObj['error'] === UPLOAD_ERR_OK) {
            $name = time(). '_' . basename($fileObj['name']);
            if (!is_dir('uploads/assignments')) mkdir('uploads/assignments', 0777, true);
            move_uploaded_file($fileObj['tmp_name'], 'uploads/assignments/' . $name);
            
            $stmt = $pdo->prepare('INSERT INTO assignments (title, file_path, teacher_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
            $stmt->execute([$title, $name, $u->id]);
            $_SESSION['success'] = "Giao bài tập thành công";
        }
    }
    header('Location: assignments.php');
    exit;
}

if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = $_GET['id'];
    $fileObj = $_FILES['file'] ?? null;
    if ($fileObj && $fileObj['error'] === UPLOAD_ERR_OK) {
        $name = time() . '_' . basename($fileObj['name']);
        if (!is_dir('uploads/submissions')) mkdir('uploads/submissions', 0777, true);
        move_uploaded_file($fileObj['tmp_name'], 'uploads/submissions/' . $name);
        
        $stmt = $pdo->prepare('INSERT INTO submissions (assignment_id, student_id, file_path, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->execute([$id, $u->id, $name]);
        $_SESSION['success'] = "Nộp bài thành công";
    }
    header('Location: assignments.php');
    exit;
}

if ($action === 'show') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT file_path, title FROM assignments WHERE id = ?');
    $stmt->execute([$id]);
    $assign = $stmt->fetch();
    if ($assign) {
        $path = 'uploads/assignments/' . $assign->file_path;
        if (file_exists($path)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($assign->file_path).'"');
            readfile($path);
            exit;
        }
    }
    die("File không tồn tại");
}

if ($action === 'download_submission') {
    $id = $_GET['id'];
    if ($u->role === 'teacher') {
        $stmt = $pdo->prepare('SELECT file_path FROM submissions WHERE id = ?');
        $stmt->execute([$id]);
        $sub = $stmt->fetch();
        if ($sub) {
            $path = 'uploads/submissions/' . $sub->file_path;
            if (file_exists($path)) {
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($sub->file_path).'"');
                readfile($path);
                exit;
            }
        }
    }
    die("Không có quyền hoặc file không tồn tại");
}

$stmt = $pdo->query('SELECT * FROM assignments ORDER BY created_at DESC');
$assignments = $stmt->fetchAll();

require 'layouts/app.php';
?>
<h2>Quản lý Bài tập</h2>
<?php if($u->role == "teacher"): ?>
<div class="card mb-4">
    <div class="card-header">Giao bài mới</div>
    <div class="card-body">
        <form action="assignments.php?action=store" method="POST" enctype="multipart/form-data">                                                                        
            <?= csrf() ?>
            <input type="text" name="title" class="form-control mb-2" placeholder="Tiêu đề" required>                                                                    
            <input type="file" name="file" class="form-control mb-2" required>
            <button class="btn btn-primary">Tải lên</button>
        </form>
    </div>
</div>
<?php endif; ?>

<h4>Danh sách bài tập</h4>
<div class="list-group">
    <?php foreach($assignments as $assignment): ?>
    <div class="list-group-item">
        <h5><?= htmlspecialchars($assignment->title) ?> <a href="assignments.php?action=show&id=<?= $assignment->id ?>" class="btn btn-sm btn-info float-end">Tải đề</a></h5>       
        <?php if($u->role == "student"): ?>
        <form action="assignments.php?action=submit&id=<?= $assignment->id ?>" method="POST" enctype="multipart/form-data" class="mt-2">                                         
            <?= csrf() ?>
            <label>Nộp bài:</label>
            <input type="file" name="file" required>
            <button class="btn btn-sm btn-success">Nộp</button>
        </form>
        <?php else: ?>
        <div class="mt-3">
            <h6>Bài nộp của sinh viên:</h6>
            <ul>
                <?php 
                $s_stmt = $pdo->prepare('SELECT submissions.*, users.name, users.username FROM submissions JOIN users ON submissions.student_id = users.id WHERE assignment_id = ?');
                $s_stmt->execute([$assignment->id]);
                $subs = $s_stmt->fetchAll();
                foreach($subs as $sub): 
                ?>
                <li><?= htmlspecialchars($sub->name ?? $sub->username) ?> - <a href="assignments.php?action=download_submission&id=<?= $sub->id ?>" target="_blank">Xem bài</a></li>                                                                                            
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
</body></html>