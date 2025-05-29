@props(['todo' => null])

<div class="modal fade" id="todoModal" tabindex="-1" aria-labelledby="todoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="todoModalLabel">
                        할일 상세보기
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="닫기"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="todoText" class="form-label fw-semibold">
                            내용 <span class="text-danger"></span>
                        </label>
                        <input type="text"
                            class="form-control"
                            id="todoText"
                            name="todo_text"
                            maxlength="20"
                            placeholder="제목을 입력해주세요 (20자 이하)"
                            value="{{ old('todo_text', $todo->todo_text ?? '') }}"
                            readonly
                            style="background-color: white;"
                            required>
                    </div>
                </div>

                <div class="modal-footer" id="todoModalFooter">
                </div>
            </form>
        </div>
    </div>
</div>
