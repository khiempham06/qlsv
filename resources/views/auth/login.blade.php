<!DOCTYPE html>
<html>
<head><title>Đăng nhập</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center">Đăng nhập</h3>
            @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                <div class="mb-3"><label>Mật khẩu</label><input type="password" name="password" class="form-control" required></div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>
    </div>
</body>
</html>