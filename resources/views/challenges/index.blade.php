
@extends("layouts.app")
@section("content")
<h2>Trò chơi giải đố</h2>

@if(auth()->user()->role == "teacher")
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning">Tạo quizz Mới</div>
    <div class="card-body">
        <form action="{{ route('challenges.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3"><label>Gợi ý:</label><textarea name="hint" class="form-control" required></textarea></div>
            <div class="mb-3"><label>File đáp án:</label><input type="file" name="file_txt" accept=".txt" class="form-control" required></div>
            <button class="btn btn-warning">Tạo câu đố</button>
        </form>
    </div>
</div>
@endif

<div class="row">
    @foreach($challenges as $challenge)
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Câu #{{ $challenge->id }}</h5>
                <p><strong>Gợi ý:</strong> {{ $challenge->hint }}</p>
                @if(auth()->user()->role == "student")
                <form action="{{ route('challenges.answer', $challenge->id) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="text" name="answer" class="form-control" placeholder="Nhập đáp án" required>
                        <button class="btn btn-success">Đáp án</button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
