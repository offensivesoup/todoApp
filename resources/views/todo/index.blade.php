@extends('layout.app')

@section('content')

<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <h4 class="text-center">해야 할 일</h4>
            <div id="todo" class="drop-zone bg-light p-3 rounded" ondrop="onDrop(event, 0)" ondragover="onDragOver(event)">
                @foreach ($todoList->where('is_complete', 0) as $todo)
                    <x-list-item :todo="$todo" />
                @endforeach
                <div class="text-center mt-3 create-button">
                    <button class="btn btn-outline-primary btn-sm" onclick="openTodoModal('create')">
                        추가하기
                    </button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <h4 class="text-center">진행 중</h4>
            <div id="doing" class="drop-zone bg-light p-3 rounded" ondrop="onDrop(event, 1)" ondragover="onDragOver(event)">
                @foreach ($todoList->where('is_complete', 1) as $todo)
                    <x-list-item :todo="$todo" />
                @endforeach
            </div>
        </div>
        <div class="col-md-4">
            <h4 class="text-center">완료됨</h4>
            <div id="done" class="drop-zone bg-light p-3 rounded" ondrop="onDrop(event, 2)" ondragover="onDragOver(event)">
                @foreach ($todoList->where('is_complete', 2) as $todo)
                    <x-list-item :todo="$todo" />
                @endforeach
            </div>
        </div>
    </div>
</div>

<x-todo-modal />

@endsection

@push('scripts')

{{-- 할 일 상태 변경 로직 --}}
<script>
    let draggedTodoId = null;
    let dropPreviewLine = document.createElement('div');
    dropPreviewLine.className = 'drop-preview-line';

    function onDragStart(event, todoId) {
        draggedTodoId = todoId;

        const todoEl = document.getElementById('todo-' + todoId);
        todoEl.classList.add('dragging');
    }

    function onDragOver(event) {
        event.preventDefault();

        const targetZone = event.currentTarget;
        const draggingEl = document.getElementById('todo-' + draggedTodoId);
        const y = event.clientY;

        const items = [...targetZone.querySelectorAll('.todo-item')].filter(el => el !== draggingEl && el !== dropPreviewLine);

        let closest = null;
        let closestOffset = Number.POSITIVE_INFINITY;

        for (const item of items) {
            const box = item.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;

            if (offset < 0 && Math.abs(offset) < closestOffset) {
                closestOffset = Math.abs(offset);
                closest = item;
            }
        }

        if (closest) {
            targetZone.insertBefore(dropPreviewLine, closest);
        } else {
            const createBtn = targetZone.querySelector('.create-button');
            if (createBtn) {
                targetZone.insertBefore(dropPreviewLine, createBtn);
            } else {
                targetZone.appendChild(dropPreviewLine);
            }
        }
    }

    async function onDrop(event, newStatus) {
        event.preventDefault();

        const todoEl = document.getElementById('todo-' + draggedTodoId);
        todoEl.classList.remove('dragging');

        // 우선순위 계산
        let newPriority = 1;
        const dropZone = dropPreviewLine.closest('.drop-zone');
        const siblings = [...dropZone.querySelectorAll('.todo-item')].filter(el => el !== todoEl);
        if (siblings.length === 0) {
            newPriority = 1;
        } else {
            const index = siblings.findIndex(sibling =>
                dropPreviewLine.compareDocumentPosition(sibling) & Node.DOCUMENT_POSITION_FOLLOWING
            );
            if (index === -1) {
                newPriority = siblings.length + 1;
            } else {
                newPriority = index + 1;
            }
        }

        if (dropPreviewLine.parentNode) {
            dropPreviewLine.parentNode.insertBefore(todoEl, dropPreviewLine);
            dropPreviewLine.remove();
        }

        const currentStatus = todoEl.dataset.todo.is_complete;
        const currentPriority = todoEl.dataset.todo.priority;

        if (currentStatus === newStatus && currentPriority === newPriority) {
            return;
        } else {
            showLoading();
            try {
                if (currentPriority !== newPriority) {
                    const res = await fetch(`/todo/${draggedTodoId}/update-priority`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ is_complete: newStatus, priority: newPriority })
                    });

                    const data = await res.json();
                    if (!res.ok || !data.success) {
                        alert('잠시 후 시도해주세요.');
                        location.reload();
                        return;
                    }
                }

                if (currentStatus !== newStatus) {
                    const res = await fetch(`/todo/${draggedTodoId}/update-complete`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ is_complete: newStatus })
                    });

                    if (!res.ok) {
                        alert('잠시 후 시도해주세요.');
                        location.reload();
                        return;
                    }
                }
            } catch (e) {
                alert('오류가 발생했습니다.');
                location.reload();
            } finally {
                hideLoading();
            }
        }
    }
</script>

{{-- 모달 관련 로직 --}}
<script>
    let currentTodo = null;
    let isEditMode = false;

    function handleTodoClick(el) {
        const todo = JSON.parse(el.dataset.todo);
        openTodoModal('detail', todo);
    }

    function handleEnterKey(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (isEditMode) {
                updateTodo();
            } else if (!currentTodo) {
                createTodo();
            }
        }
    }

    function openTodoModal(mode, todo = null) {
        const modalElement = document.getElementById('todoModal');
        const modal = new bootstrap.Modal(modalElement);
        const todoTextInput = modalElement.querySelector('#todoText');
        const modalTitle = modalElement.querySelector('#todoModalLabel');
        const footer = modalElement.querySelector('#todoModalFooter');

        currentTodo = todo;
        isEditMode = false;

        modalElement.removeEventListener('keydown', handleEnterKey);
        modalElement.addEventListener('keydown', handleEnterKey);

        if (mode === 'create') {
            modalTitle.textContent = '할 일 추가하기';
            todoTextInput.value = '';
            todoTextInput.readOnly = false;
            todoTextInput.style.backgroundColor = 'white';

            footer.innerHTML = `
                <button type="button" class="btn btn-secondary" onclick="closeTodoModal()">닫기</button>
                <button type="button" class="btn btn-primary" onclick="createTodo()">추가</button>
            `;
        } else if (mode === 'detail') {
            modalTitle.textContent = '할일 상세보기';
            if (todoTextInput && todo) {
                todoTextInput.value = todo.todo_text || '';
                todoTextInput.readOnly = true;
                todoTextInput.style.backgroundColor = 'white';
            }

            footer.innerHTML = `
                <button type="button" class="btn btn-secondary" onclick="closeTodoModal()">닫기</button>
                <button type="button" class="btn btn-primary" onclick="enableEditMode()">수정</button>
            `;
        }

        modal.show();
    }

    function closeTodoModal() {
        const modalElement = document.getElementById('todoModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        modal.hide();
    }

    function enableEditMode() {
        const modalElement = document.getElementById('todoModal');
        const todoTextInput = modalElement.querySelector('#todoText');
        const footer = modalElement.querySelector('#todoModalFooter');

        todoTextInput.readOnly = false;
        isEditMode = true;

        footer.innerHTML = `
            <button type="button" class="btn btn-secondary" onclick="closeTodoModal()">닫기</button>
            <button type="button" class="btn btn-success" onclick="updateTodo()">저장</button>
        `;
    }    
</script>

{{-- 추가 수정 삭제 로직 --}}
<script>

    function validation() {
        let check = true;
        const todoTextInput = document.getElementById('todoText');
        const newText = todoTextInput.value.trim();
        const allTodos = document.querySelectorAll('.todo-item');

        if (currentTodo && newText === currentTodo.todo_text) {
                alert("내용이 변경되지 않았습니다.");
                check = false;
        } else if (!newText) {
            alert("할 일 내용은 공백이 될 수 없습니다.");
            check = false;
        } else if (newText.length > 20) {
            alert("할 일 내용은 20자 이하로 입력해주세요.");
            check = false;
        } else {
            for (let item of allTodos) {
                const todoData = JSON.parse(item.dataset.todo);
                if (todoData.todo_text === newText) {
                    alert("같은 내용의 할 일이 이미 존재합니다.");
                    check = false;
                }
            }
        }

        if (!check) {
            hideLoading();
        } 

        return check;

    }

    function createTodo() {

        showLoading();

        const todoTextInput = document.getElementById('todoText');
        const newText = todoTextInput.value.trim();

        if (validation()) {
            fetch(`/todo`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    todo_text: newText
                })
            })
            .then(async res => {
                const contentType = res.headers.get("content-type");

                if (!res.ok) {
                    const errMsg = contentType && contentType.includes("application/json")
                        ? (await res.json()).message
                        : await res.text();
                    throw new Error(errMsg || '요청에 실패했습니다.');
                } else {
                    alert("추가되었습니다.");
                    location.reload();
                }
            })
            .catch(err => {
                alert("오류가 발생했습니다.");
            })
            .finally(() => {
                hideLoading();
            });
        }
    }

    function updateTodo() {

        showLoading();

        const modalElement = document.getElementById('todoModal');
        const todoTextInput = modalElement.querySelector('#todoText');
        const newText = todoTextInput.value.trim();

        if (validation()) {
            fetch(`/todo/${currentTodo.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    todo_text: newText
                })
            })
            .then(async res => {
                const contentType = res.headers.get("content-type");

                if (!res.ok) {
                    const errMsg = contentType && contentType.includes("application/json")
                        ? (await res.json()).message
                        : await res.text();
                    throw new Error(errMsg || '요청에 실패했습니다.');
                } else {
                    alert("수정되었습니다.");
                    location.reload();
                }
            })
            .catch(err => {
                alert("오류가 발생했습니다.");
            })
            .finally(() => {
                hideLoading();
            });
        }
    }

    function deleteTodo(event, todoId) {

        showLoading();

        event.stopPropagation();

        if (!confirm("정말 삭제하시겠습니까?")) return;

        fetch(`/todo/${todoId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        }).then(response => {
            if (response.ok) {
                document.getElementById('todo-' + todoId).remove();
            } else {
                alert('삭제에 실패했습니다.');
            }
        })
        .finally(() => {
            hideLoading();
        });
    }
</script>
  
@endpush 