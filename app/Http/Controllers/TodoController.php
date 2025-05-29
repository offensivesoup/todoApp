<?php

namespace App\Http\Controllers;


use App\Models\Todo;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    // Todo 메인 화면 (목록 조회)
    public function index()
    {
        $todoList = Todo::orderBy("priority")->paginate();
        return view('todo.index', compact('todoList'));
    }

    // Todo 상세 화면 (상세 조회)
    public function show(string $id)
    {
        $todo = Todo::find($id);
        return view('todo.detail', compact('todo'));
    }

    // Todo 생성 화면
    public function create()
    {
        return view('todo.form', ['todo' => null]);
    }

    // Todo 수정 화면
    public function edit(string $id)
    {
        $todo = Todo::findOrFail($id);
        return view('todo.form', compact('todo'));
    }

    // Todo 생성
    public function store(Request $request)
    {
        $maxPriority = Todo::where('is_complete', 0)->max('priority');

        $newPriority = $maxPriority !== null ? $maxPriority + 1 : 1;
    
        $validated = $request->validate([
            'todo_text' => 'required|string|max:20',
        ]);

        $today = now()->toDateString();

        $duplicate = Todo::whereDate('created_at', $today)
            ->where('todo_text', $validated['todo_text'])
            ->exists();

        if ($duplicate) {
            $errorMessage = '같은 날짜에 동일한 할 일이 이미 존재합니다.';

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                ], 409);
            }

            return back()->withErrors($errorMessage)->withInput();
        }

        $todo = new Todo([
            'todo_text' => $validated['todo_text'],
            'priority' => $newPriority,
        ]);
    
        $saveSuccess = $todo->save();
    
        if ($saveSuccess) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '추가되었습니다.',
                ]);
            }
            return redirect()->route('todo.index')->with('success', '할 일이 추가되었습니다.');
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '추가에 실패했습니다.',
                ]);
            }
            return back()->withErrors('추가에 실패했습니다.');
        }
    }
    
    // Todo 수정
    public function update(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);
    
        $validated = $request->validate([
            'todo_text' => 'required|string|max:20',
        ]);
    
        $updateSuccess = $todo->update($validated);
    
        if ($updateSuccess) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '수정되었습니다.',
                ]);
            }
            return redirect()->route('todo.index')->with('success', '할 일이 수정되었습니다.');
        } else {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '수정에 실패했습니다.',
                ]);
            }
            return back()->withErrors('수정에 실패했습니다.');
        }
    }

    // Todo 삭제
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $todo = Todo::findOrFail($id);
    
            $isComplete = $todo->is_complete;
            $priority = $todo->priority;
    
            $todo->delete();
    
            Todo::where('is_complete', $isComplete)
                ->where('priority', '>', $priority)
                ->decrement('priority');
        });
    
        return response()->json(['success' => true]);
    }

    // Todo 완료여부 수정
    public function updateComplete(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);
        $todo->is_complete = $request->is_complete;
        $todo->save();

        return response()->json(['success' => true]);
    }

    // Todo 우선순위 수정
    public function updatePriority(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);
    
        $request->validate([
            'priority' => 'required|integer|min:1',
            'is_complete' => 'required|integer|min:0|max:2',
        ]);
    
        $newPriority = $request->priority;
        $newStatus = $request->is_complete;
    
        DB::transaction(function () use ($todo, $newPriority, $newStatus) {
            $oldStatus = $todo->is_complete;
            $oldPriority = $todo->priority;
    
            if ($oldStatus === $newStatus) {
                // 같은 상태 내에서 priority 변경
                if ($newPriority < $oldPriority) {
                    Todo::where('is_complete', $newStatus)
                        ->whereBetween('priority', [$newPriority, $oldPriority - 1])
                        ->increment('priority');
                } else if ($newPriority > $oldPriority) {
                    Todo::where('is_complete', $newStatus)
                        ->whereBetween('priority', [$oldPriority + 1, $newPriority])
                        ->decrement('priority');
                }
            } else {
                // 다른 상태로 이동
                Todo::where('is_complete', $oldStatus)
                    ->where('priority', '>', $oldPriority)
                    ->decrement('priority');
    
                Todo::where('is_complete', $newStatus)
                    ->where('priority', '>=', $newPriority)
                    ->increment('priority');
            }
    
            $todo->priority = $newPriority;
            $todo->is_complete = $newStatus;
            $todo->save();
        });
    
        return response()->json(['success' => true]);
    }    
}
