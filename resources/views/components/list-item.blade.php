@props(['todo'])

<div class="card mb-2 p-2 draggable todo-item position-relative"
     draggable="true"
     onclick="handleTodoClick(this)"
     ondragstart="onDragStart(event, {{ $todo->id }})"
     data-todo='@json($todo)'
     data-id="{{ $todo->id }}" 
     id="todo-{{ $todo->id }}">
    <button onclick="deleteTodo(event, {{ $todo->id }})"
            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
            title="삭제">
        &times;
    </button>
    <strong>{{ $todo->todo_text }}</strong><br>
</div>