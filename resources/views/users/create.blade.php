
@extends("layouts.app")
@section("content")
<h2>Thêm sinh viên</h2>
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
    <div class="mb-3"><label>Mật khẩu</label><input type="password" name="password" class="form-control" required></div>
    <div class="mb-3"><label>Họ tên</label><input type="text" name="name" class="form-control"></div>
    <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
    <div class="mb-3"><label>SĐT</label><input type="text" name="phone" class="form-control"></div>
    <div class="mb-3"><label>Quyền</label><select name="role" class="form-control"><option value="student">Student</option><option value="teacher">Teacher</option></select></div>
    <button class="btn btn-primary">Lưu</button>
</form>
@endsection
