@extends("layouts.app")
@section("content")
<h2>Quản lý Bài tập</h2>
@if(auth()->user()->role == "teacher")
<div class="card mb-4">
    <div class="card-header">Giao bài mới</div>
    <div class="card-body">
        <form action="{{ route('assignments.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="text" name="title" class="form-control mb-2" placeholder="Tiêu đề" required>
            <input type="file" name="file" class="form-control mb-2" required>
            <button class="btn btn-primary">Tải lên</button>
        </form>
    </div>
</div>
@endif

<h4>Danh sách bài tập</h4>
<div class="list-group">
    @foreach($assignments as $assignment)
    <div class="list-group-item">
        <h5>{{ $assignment->title }} <a href="{{ route('assignments.show', $assignment->id) }}" class="btn btn-sm btn-info float-end">Tải đề</a></h5>

        @if(auth()->user()->role == "student")
        <form action="{{ route('assignments.submit', $assignment->id) }}" method="POST" enctype="multipart/form-data" class="mt-2">
            @csrf
            <label>Nộp bài:</label>
            <input type="file" name="file" required>
            <button class="btn btn-sm btn-success">Nộp</button>
        </form>
        @else
        <div class="mt-3">
            <h6>Bài nộp của sinh viên:</h6>
            <ul>
                @foreach($assignment->submissions as $sub)
                <li>{{ $sub->student->name ?? $sub->student->username }} - <a href="{{ route('submissions.download', $sub->id) }}" target="_blank">Xem bài</a></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endforeach
</div>
@endsection