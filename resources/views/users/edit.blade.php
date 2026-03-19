
@extends("layouts.app")
@section("content")
<h2>Sửa thông tin</h2>
<form method="POST" action="{{ route('users.update', $user->id) }}">
    @csrf @method("PUt")
    <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" value="{{ $user->username }}" {{ auth()->user()->role == "teacher" ? "" : "readonly" }}></div>
    <div class="mb-3"><label>Mật khẩu (để trống nếu không đổi)</label><input type="password" name="password" class="form-control"></div>
    <div class="mb-3"><label>Họ tên</label><input type="text" name="name" class="form-control" value="{{ $user->name }}" {{ auth()->user()->role == "teacher" ? "" : "readonly" }}></div>
    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="{{ $user->email }}"></div>
    <div class="mb-3"><label>SĐT</label><input type="text" name="phone" class="form-control" value="{{ $user->phone }}"></div>
    <button class="btn btn-primary">Lưu</button>
</form>
@endsection
