<!DOCTYPE html>
<html>
<head><title>Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
    <h2>Xin chào, {{ auth()->user()->name }} ({{ auth()->user()->role }})</h2>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-danger">Đăng xuất</button></form>
    <hr>
    <a href="{{ route('users.index') }}" class="btn btn-info">Quản lý người dùng</a>
    <a href="{{ route('assignments.index') }}" class="btn btn-primary">Bài tập</a>
    <a href="{{ route('challenges.index') }}" class="btn btn-warning">Giải đố</a>
</body>
</html>