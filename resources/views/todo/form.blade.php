@extends('layout.app')

@section('content')

<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="card shadow" style="min-width: 400px; max-width: 600px; padding: 2rem;">
        <h3 class="text-center mb-4 fw-bold">
            {{ isset($todo) ? '할일 수정하기' : '할일 추가하기' }}
        </h3>

        <form action="{{ isset($todo) ? route('todo.update', $todo->id) : route('todo.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf
            @if(isset($todo))
                @method('PUT')
            @endif

            <div class="mb-4">
                <label for="todoText" class="form-label fw-semibold">내용<span class="text-danger">*</span></label>
                <input type="text"
                       class="form-control @error('todo_text') is-invalid @enderror"
                       id="todoText"
                       name="todo_text"
                       maxlength="20"
                       placeholder="할일을 입력해주세요 (20자 이하)"
                       value="{{ old('todo_text', $todo->todo_text ?? '') }}"
                       required>
                @error('todo_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                    {{ isset($todo) ? '수정' : '저장' }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
