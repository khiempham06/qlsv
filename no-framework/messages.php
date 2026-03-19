<?php
require 'config.php';
header('Content-Type: application/json');

if (!check()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$u = user();

if ($action === 'fetch') {
    $id = $_GET['id'] ?? 0;
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC');
    $stmt->execute([$u->id, $id, $id, $u->id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'store') {
    $receiver = $_POST['receiver_id'] ?? 0;
    $content = $_POST['content'] ?? '';
    if ($content && $receiver) {
        $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())');
        $stmt->execute([$u->id, $receiver, $content]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if ($action === 'update') {
    $id = $_GET['id'] ?? 0;
    $content = $_POST['content'] ?? '';
    $stmt = $pdo->prepare('UPDATE messages SET content = ?, updated_at = NOW() WHERE id = ? AND sender_id = ?');
    $stmt->execute([$content, $id, $u->id]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'destroy') {
    $id = $_GET['id'] ?? 0;
    if ($u->role === 'teacher') {
        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ?');
        $stmt->execute([$id]);
    } else {
        $stmt = $pdo->prepare('DELETE FROM messages WHERE id = ? AND sender_id = ?');
        $stmt->execute([$id, $u->id]);
    }
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>