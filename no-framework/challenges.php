<?php
require 'config.php';
if (!check()) { header('Location: login.php'); exit; }
$u = user();

$action = $_GET['action'] ?? 'index';

if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if ($u->role === 'teacher') {
        $stmt = $pdo->prepare('INSERT INTO challenges (teacher_id, hint, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
        $stmt->execute([$u->id, $_POST['hint']]);
        $id = $pdo->lastInsertId();

        $fileObj = $_FILES['file_txt'] ?? null;
        if ($fileObj && $fileObj['error'] === UPLOAD_ERR_OK) {
            $filename = strtolower($fileObj['name']);
            $dir = 'uploads/challenges/' . $id;
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            move_uploaded_file($fileObj['tmp_name'], $dir . '/' . $filename);
        }
        $_SESSION['success'] = "Quizz tạo thành công";
    }
    header('Location: challenges.php');
    exit;
}

if ($action === 'answer' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = $_GET['id'];
    $answer = strtolower($_POST['answer'] . '.txt');
    $dir = 'uploads/challenges/' . $id;
    
    if (is_dir($dir)) {
        $files = scandir($dir);
        $files = array_diff($files, array('.', '..'));
        
        if (count($files) > 0) {
            $files = array_values($files);
            $actual = $files[0];
            if ($answer === strtolower($actual)) {
                $content = file_get_contents($dir . '/' . $actual);
                $_SESSION['success'] = "Chuẩn! " . $content;
            } else {
                $_SESSION['error'] = "Sai rồi!";
            }
        } else {
            $_SESSION['error'] = "Sai rồi!";
        }
    } else {
        $_SESSION['error'] = "Sai rồi!";
    }
    header('Location: challenges.php');
    exit;
}

$stmt = $pdo->query('SELECT * FROM challenges');
$challenges = $stmt->fetchAll();

require 'layouts/app.php';
?>
<h2>Trò chơi giải đố</h2>

<?php if($u->role == "teacher"): ?>
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning">Tạo quizz Mới</div>
    <div class="card-body">
        <form action="challenges.php?action=store" method="POST" enctype="multipart/form-data">                                                                         
            <?= csrf() ?>
            <div class="mb-3"><label>Gợi ý:</label><textarea name="hint" class="form-control" required></textarea></div>                                                 
            <div class="mb-3"><label>File đáp án:</label><input type="file" name="file_txt" accept=".txt" class="form-control" required></div>                           
            <button class="btn btn-warning">Tạo câu đố</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <?php foreach($challenges as $challenge): ?>
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Câu #<?= $challenge->id ?></h5>
                <p><strong>Gợi ý:</strong> <?= htmlspecialchars($challenge->hint) ?></p>        
                <?php if($u->role == "student"): ?>
                <form action="challenges.php?action=answer&id=<?= $challenge->id ?>" method="POST">                                                                                      
                    <?= csrf() ?>
                    <div class="input-group">
                        <input type="text" name="answer" class="form-control" placeholder="Nhập đáp án" required>                                                                  
                        <button class="btn btn-success">Đáp án</button>       
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
</body></html>