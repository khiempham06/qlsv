
<!DOCTYPE html>
<html>
<head>
    <title>Quản lý Lớp học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route("dashboard") }}">Home</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.index') }}">Người dùng</a></li>
                    @if(auth()->check())
                    <li class="nav-item"><a class="nav-link" href="{{ route('users.edit', auth()->id()) }}">Cài đặt tài khoản</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link" href="{{ route('assignments.index') }}">Bài tập</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('challenges.index') }}">Câu đố</a></li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">Hi, {{ auth()->user()->name ?? auth()->user()->username }}</span>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-sm btn-danger">Đăng xuất</button></form>
                </div>
            </div>
        </div>
    </nav>
    @if(session("success")) <div class="alert alert-success">{{ session("success") }}</div> @endif
    @if(session("error")) <div class="alert alert-danger">{{ session("error") }}</div> @endif
    @yield("content")
</body>
</html>
