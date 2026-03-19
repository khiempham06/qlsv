
@extends("layouts.app")
@section("content")
<style>
.chat-container {
    height: 400px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    padding: 15px;
    background-color: #f0f2f5;
}
.chat-bubble {
    max-width: 75%;
    margin-bottom: 10px;
    padding: 10px 15px;
    border-radius: 18px;
    position: relative;
    word-wrap: break-word;
}
.chat-sent {
    align-self: flex-end;
    background-color: #0084ff;
    color: white;
    border-bottom-right-radius: 4px;
}
.chat-received {
    align-self: flex-start;
    background-color: #e4e6eb;
    color: black;
    border-bottom-left-radius: 4px;
}
.chat-actions {
    font-size: 0.75rem;
    margin-top: 3px;
}
.chat-actions button {
    background: none; border: none; padding: 0; color: inherit; font-size: 0.75rem;
}
</style>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-white">Thông tin cá nhân</div>
            <div class="card-body">
                <p><strong>Tên:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>SĐT:</strong> {{ $user->phone }}</p>
                <p><strong>Vai trò:</strong> <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-bold d-flex align-items-center">
                @if(auth()->id() == $user->id)
                    🗄 Khung lưu trữ cá nhân (Cloud của tôi)
                @else
                    💬 Chat với {{ $user->name ?? $user->username }}
                @endif
            </div>

            <div class="card-body p-0">
                <div class="chat-container box-shadow-inner" id="chatbox">
                    @if(auth()->id() == $user->id)
                        <div class="alert alert-info m-3 text-center">
                            Đây là trang xem trước giao diện cá nhân của bạn.<br>
                            Để bắt đầu nhắn tin với người khác (như Messenger), vui lòng vào mục <strong>Danh bạ (Users)</strong> và chọn một người dùng khác!
                        </div>
                    @endif

                    @foreach($messages as $msg)
                        @if($msg->sender_id == auth()->id())
                            <!-- Tin nhắn của MÌNH gửi -->
                            <div class="chat-bubble chat-sent">
                                <div>{{ $msg->content }}</div>
                                <div class="chat-actions text-end text-white-50">
                                    <small>{{ $msg->created_at->format('H:i d/m') }}</small> -
                                    <button onclick="editMode({{ $msg->id }}, '{{ $msg->content }}')" class="text-white-50 text-decoration-underline">Sửa</button> -
                                    <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline">@csrf @method("DELETE")<button type="submit" class="text-white-50 text-decoration-underline" onclick="return confirm('Thu hồi tin nhắn?')">Xóa</button></form>
                                </div>
                            </div>
                        @else
                            <!-- Tin nhắn của ĐỐI PHƯƠNG gửi -->
                            <div class="chat-bubble chat-received">
                                <div>{{ $msg->content }}</div>
                                <div class="chat-actions text-start text-muted">
                                    <small>{{ $msg->created_at->format('H:i d/m') }}</small>
                                    @if(auth()->user()->role == "teacher")
                                    - <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline">@csrf @method("DELETE")<button type="submit" class="text-danger">Thu hồi (GV)</button></form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="p-3 bg-white border-top">
                    @if(auth()->id() != $user->id)
                    <!-- Khung Nhắn tin Mới -->
                    <form method="POST" action="{{ route('messages.store') }}" class="d-flex" id="msgForm">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $user->id }}">
                        <input type="text" name="content" class="form-control rounded-pill me-2 px-3" required autocomplete="off" placeholder="Nhập tin nhắn...">
                        <button class="btn btn-primary rounded-pill px-4">Gửi</button>
                    </form>

                    <!-- Khung Chỉnh sửa (Ẩn mặc định) -->
                    <form method="POST" action="" class="d-none mt-2" id="editForm">
                        @csrf @method("PUT")
                        <div class="input-group">
                            <span class="input-group-text bg-warning text-dark border-0">Sửa tin nhắn</span>
                            <input type="text" name="content" id="editInput" class="form-control" required autocomplete="off">
                            <button class="btn btn-success">Lưu</button>
                            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Hủy</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var chatbox = document.getElementById('chatbox');
    var msgForm = document.getElementById('msgForm');
    const currentUserId = {{ auth()->id() }};
    const chatUserId = {{ $user->id }};
    const isTeacher = {{ auth()->user()->role == 'teacher' ? 'true' : 'false' }};
    let lastRenderedCount = {{ count($messages) }};
    let lastRenderedStr = "";

    function scrollToBottom() {
        chatbox.scrollTop = chatbox.scrollHeight;
    }
    scrollToBottom();

    if(msgForm) {
        msgForm.addEventListener('submit', function(e) {
            e.preventDefault();
            let formData = new FormData(this);
            fetch(this.action, {
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
    }

    function fetchMessages() {
        if(currentUserId == chatUserId) return;

        fetch('/messages/fetch/' + chatUserId)
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
                        <form action='/messages/${msg.id}' method='POST' class='d-inline'>
                            <input type='hidden' name='_token' value='{{ csrf_token() }}'>
                            <input type='hidden' name='_method' value='DELETE'>
                            <button type='submit' class='text-white-50 text-decoration-underline' onclick="return confirm('Thu hồi tin nhắn?')">Xóa</button>
                        </form>
                    </div>
                `;
            } else {
                bubble.className = 'chat-bubble chat-received';
                let btnXoa = '';
                if(isTeacher) {
                    btnXoa = `- <form action='/messages/${msg.id}' method='POST' class='d-inline'>
                                <input type='hidden' name='_token' value='{{ csrf_token() }}'>
                                <input type='hidden' name='_method' value='DELETE'>
                                <button type='submit' class='text-danger'>Thu hồi (GV)</button>
                              </form>`;
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
        setInterval(fetchMessages, 2000);
    }

    function editMode(id, content) {
        if(msgForm) msgForm.classList.add('d-none');
        var editForm = document.getElementById('editForm');
        editForm.classList.remove('d-none');
        editForm.classList.add('d-flex');
        editForm.action = '/messages/' + id;
        document.getElementById('editInput').value = content;
        document.getElementById('editInput').focus();
    }

    function cancelEdit() {
        if(msgForm) msgForm.classList.remove('d-none');
        var editForm = document.getElementById('editForm');
        editForm.classList.add('d-none');
        editForm.classList.remove('d-flex');
    }
</script>
@endsection
