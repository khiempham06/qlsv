<?php
require 'config.php';
if (!check()) { header('Location: login.php'); exit; }
$u = user();

$action = $_GET['action'] ?? 'index';

if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = $pdo->prepare('INSERT INTO users (username, password, name, email, phone, role) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $_POST['username'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['name'],
        $_POST['email'],
        $_POST['phone'],
        $_POST['role']
    ]);
    $_SESSION['success'] = 'Đã thêm thành công';
    header('Location: users.php');
    exit;
}

if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = $_GET['id'];
    if ($u->role !== 'teacher' && $u->id != $id) {
        die('Unauthorized');
    }
    
    $passwordSql = '';
    $params = [$_POST['username'], $_POST['name'], $_POST['email'], $_POST['phone']];
    
    if (!empty($_POST['password'])) {
        $passwordSql = ', password = ?';
        $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }
    $params[] = $id;

    $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, email = ?, phone = ? $passwordSql WHERE id = ?");
    $stmt->execute($params);

    $_SESSION['success'] = 'Cập nhật thành công';
    header('Location: users.php');
    exit;
}

if ($action === 'destroy' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = $_GET['id'];
    if ($u->role === 'teacher' && $u->id != $id) {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['success'] = 'Đã xóa người dùng';
    }
    header('Location: users.php');
    exit;
}

if ($action === 'show') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $targetUser = $stmt->fetch();
    
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY created_at ASC');
    $stmt->execute([$u->id, $id, $id, $u->id]);
    $messages = $stmt->fetchAll();
    
    require 'layouts/app.php';
    ?>
    <style>
    .chat-container { height: 400px; overflow-y: auto; display: flex; flex-direction: column; padding: 15px; background-color: #f0f2f5; }
    .chat-bubble { max-width: 75%; margin-bottom: 10px; padding: 10px 15px; border-radius: 18px; position: relative; word-wrap: break-word; }
    .chat-sent { align-self: flex-end; background-color: #0084ff; color: white; border-bottom-right-radius: 4px; }
    .chat-received { align-self: flex-start; background-color: #e4e6eb; color: black; border-bottom-left-radius: 4px; }
    .chat-actions { font-size: 0.75rem; margin-top: 3px; }
    .chat-actions button { background: none; border: none; padding: 0; color: inherit; font-size: 0.75rem; text-decoration: underline; cursor: pointer;}
    </style>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-white">Thông tin cá nhân</div>        
                <div class="card-body">
                    <p><strong>Tên:</strong> <?= htmlspecialchars($targetUser->name ?? '') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($targetUser->email ?? '') ?></p>
                    <p><strong>SĐT:</strong> <?= htmlspecialchars($targetUser->phone ?? '') ?></p>
                    <p><strong>Vai trò:</strong> <span class="badge bg-secondary"><?= ucfirst($targetUser->role) ?></span></p>                                                        
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom fw-bold d-flex align-items-center">                                                                                  
                    <?php if($u->id == $targetUser->id): ?>
                        Khung lưu trữ cá nhân
                    <?php else: ?>
                        Chat với <?= htmlspecialchars($targetUser->name ?? $targetUser->username) ?>        
                    <?php endif; ?>
                </div>

                <div class="card-body p-0">
                    <div class="chat-container box-shadow-inner" id="chatbox">      
                        <?php if($u->id == $targetUser->id): ?>
                            <div class="alert alert-info m-3 text-center">
                                Đây là trang xem trước giao diện cá nhân của bạn.<br>                                                                                              
                                Để bắt đầu nhắn tin với người khác (như Messenger), vui lòng vào mục <strong>Danh bạ (Users)</strong> và chọn một người dùng khác!                                                                             
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 bg-white border-top">
                        <?php if($u->id != $targetUser->id): ?>
                        <form method="POST" action="messages.php?action=store" class="d-flex" id="msgForm">                                                                             
                            <input type="hidden" name="receiver_id" value="<?= $targetUser->id ?>">                                                                                                
                            <input type="text" name="content" class="form-control rounded-pill me-2 px-3" required autocomplete="off" placeholder="Nhập tin nhắn...">                                                                                                   
                            <button class="btn btn-primary rounded-pill px-4">Gửi</button>                                                                                            
                        </form>

                        <form method="POST" action="" class="d-none mt-2" id="editForm">                                                                                                    
                            <div class="input-group">
                                <span class="input-group-text bg-warning text-dark border-0">Sửa tin nhắn</span>                                                                            
                                <input type="text" name="content" id="editInput" class="form-control" required autocomplete="off">                                                              
                                <button class="btn btn-success">Lưu</button>       
                                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Hủy</button>                                                                       
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        var chatbox = document.getElementById('chatbox');
        var msgForm = document.getElementById('msgForm');
        const currentUserId = <?= $u->id ?>;
        const chatUserId = <?= $targetUser->id ?>;
        const isTeacher = <?= $u->role === 'teacher' ? 'true' : 'false' ?>;
        
        let lastRenderedCount = 0;
        let lastRenderedStr = "";

        function scrollToBottom() {
            chatbox.scrollTop = chatbox.scrollHeight;
        }

        if(msgForm) {
            msgForm.addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                fetch('messages.php?action=store', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(r => r.json()).then(res => {
                    if(res.success) {
                        msgForm.reset();
                        fetchMessages();
                    }
                });
            });
            document.getElementById('editForm').addEventListener('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(r => r.json()).then(res => {
                    cancelEdit();
                    fetchMessages();
                });
            });
        }

        function fetchMessages() {
            if(currentUserId == chatUserId) return;

            fetch('messages.php?action=fetch&id=' + chatUserId)
                .then(res => res.json())
                .then(data => {
                    let currentStr = JSON.stringify(data.map(m => m.id + "_" + m.content + "_" + m.updated_at));
                    if(currentStr !== lastRenderedStr) {
                        let isNewData = (lastRenderedStr !== "");
                        let isNewMessage = (data.length > lastRenderedCount);
                        renderMessages(data);
                        lastRenderedStr = currentStr;
                        lastRenderedCount = data.length;
                        if(isNewMessage || !isNewData) {
                            scrollToBottom();
                        }
                    }
                });
        }

        function renderMessages(messages) {
            chatbox.innerHTML = '';
            messages.forEach(msg => {
                let dateObj = new Date(msg.created_at);
                let timeStr = dateObj.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'}) + ' ' + dateObj.toLocaleDateString('vi-VN', {day: '2-digit', month: '2-digit'});                                                             
                let bubble = document.createElement('div');

                if(msg.sender_id == currentUserId) {
                    bubble.className = 'chat-bubble chat-sent';
                    bubble.innerHTML = `
                        <div>${msg.content}</div>
                        <div class='chat-actions text-end text-white-50'>
                            <small>${timeStr}</small> -
                            <button onclick="editMode(${msg.id}, '${msg.content}')" class='text-white-50 text-decoration-underline'>Sửa</button> -                                        
                            <button onclick="deleteMsg(${msg.id})" class='text-white-50 text-decoration-underline'>Xóa</button>
                        </div>
                    `;
                } else {
                    bubble.className = 'chat-bubble chat-received';
                    let btnXoa = '';
                    if(isTeacher) {
                        btnXoa = `- <button class="text-danger" onclick="deleteMsg(${msg.id})">Thu hồi (GV)</button>`;
                    }
                    bubble.innerHTML = `
                        <div>${msg.content}</div>
                        <div class='chat-actions text-start text-muted'>
                            <small>${timeStr}</small> ${btnXoa}
                        </div>
                    `;
                }
                chatbox.appendChild(bubble);
            });
        }

        if(currentUserId != chatUserId) {
            fetchMessages();
            setInterval(fetchMessages, 2000);
        }

        function editMode(id, content) {
            if(msgForm) {
                msgForm.classList.remove('d-flex');
                msgForm.classList.add('d-none');
            }
            var editForm = document.getElementById('editForm');
            editForm.classList.remove('d-none');
            editForm.classList.add('d-flex');
            editForm.action = 'messages.php?action=update&id=' + id;
            document.getElementById('editInput').value = content;
            document.getElementById('editInput').focus();
        }

        function cancelEdit() {
            if(msgForm) {
                msgForm.classList.remove('d-none');
                msgForm.classList.add('d-flex');
            }
            var editForm = document.getElementById('editForm');
            editForm.classList.add('d-none');
            editForm.classList.remove('d-flex');
        }
        
        function deleteMsg(id) {
            if(!confirm('Thu hồi tin nhắn?')) return;
            fetch('messages.php?action=destroy&id=' + id, {method:'POST'}).then(r=>r.json()).then(res => {
                if(res.success) fetchMessages();
            });
        }
    </script>
    </body></html>
    <?php
    exit;
}

if ($action === 'create') {
    require 'layouts/app.php';
    ?>
    <h2>Thêm sinh viên</h2>
    <form method="POST" action="users.php?action=store">
        <?= csrf() ?>
        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>                                                
        <div class="mb-3"><label>Mật khẩu</label><input type="password" name="password" class="form-control" required></div>                                        
        <div class="mb-3"><label>Họ tên</label><input type="text" name="name" class="form-control"></div>                                                             
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>                                                              
        <div class="mb-3"><label>SĐT</label><input type="text" name="phone" class="form-control"></div>                                                                 
        <div class="mb-3">
            <label>Quyền</label>
            <select name="role" class="form-control">
                <option value="student">Student</option>
                <option value="teacher">Teacher</option>
            </select>
        </div>                                                                 
        <button class="btn btn-primary">Lưu</button>
    </form>
    </body></html>
    <?php
    exit;
}

if ($action === 'edit') {
    $id = $_GET['id'];
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $targetUser = $stmt->fetch();

    require 'layouts/app.php';
    ?>
    <h2>Sửa thông tin</h2>
    <form method="POST" action="users.php?action=update&id=<?= $targetUser->id ?>">
        <?= csrf() ?>
        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" value="<?= htmlspecialchars($targetUser->username) ?>" <?= $u->role == 'teacher' ? '' : 'readonly' ?>></div>                                                 
        <div class="mb-3"><label>Mật khẩu (để trống nếu không đổi)</label><input type="password" name="password" class="form-control"></div>             
        <div class="mb-3"><label>Họ tên</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($targetUser->name ?? '') ?>" <?= $u->role == 'teacher' ? '' : 'readonly' ?>></div>                                                         
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($targetUser->email ?? '') ?>"></div>                                   
        <div class="mb-3"><label>SĐT</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($targetUser->phone ?? '') ?>"></div>                                      
        <button class="btn btn-primary">Lưu</button>
    </form>
    </body></html>
    <?php
    exit;
}

$stmt = $pdo->query('SELECT * FROM users');
$users = $stmt->fetchAll();

require 'layouts/app.php';
?>
<h2>Danh sách Người dùng</h2>
<?php if($u->role == "teacher"): ?>
<a href="users.php?action=create" class="btn btn-primary mb-3">Thêm sinh viên</a>                                                                         
<?php endif; ?>
<table class="table table-bordered">
    <thead><tr><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Hành động</th></tr></thead>                                                         
    <tbody>
        <?php foreach($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user->username) ?></td>
            <td><?= htmlspecialchars($user->name ?? '') ?></td>
            <td><?= htmlspecialchars($user->email ?? '') ?></td>
            <td><?= ucfirst($user->role) ?></td>
            <td>
                <a href="users.php?action=show&id=<?= $user->id ?>" class="btn btn-info btn-sm">Xem & Nhắn tin</a>                                                                 
                <?php if($u->role == "teacher" || $u->id == $user->id): ?>                                                                                             
                    <a href="users.php?action=edit&id=<?= $user->id ?>" class="btn btn-warning btn-sm">Sửa</a>                                                                         
                <?php endif; ?>
                <?php if($u->role == "teacher" && $u->id != $user->id): ?>                                                                                             
                    <form action="users.php?action=destroy&id=<?= $user->id ?>" method="POST" style="display:inline;">                                                                       
                        <?= csrf() ?>
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa?')">Xóa</button>                                                                       
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body></html>