
@extends("layouts.app")
@section("content")
<h2>Danh sách Người dùng</h2>
@if(auth()->user()->role == "teacher")
<a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Thêm sinh viên</a>
@endif
<table class="table table-bordered">
    <thead><tr><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Hành động</th></tr></thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->username }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ ucfirst($user->role) }}</td>
            <td>
                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">Xem & Nhắn tin</a>
                @if(auth()->user()->role == "teacher" || auth()->id() == $user->id)
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                @endif
                @if(auth()->user()->role == "teacher" && auth()->id() != $user->id)
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                    @csrf @method("DELETE")
                    <button class="btn btn-danger btn-sm" onclick="return confirm('Xóa?')">Xóa</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
