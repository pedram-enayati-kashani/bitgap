<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource with filtering and search.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $cacheKey = $this->getCacheKey($user, $request);

        $tasks = Cache::remember($cacheKey, 60, function () use ($user, $request) {
            if ($user->role === 'admin') {
                $query = Task::query();
            } else {
                $query = Task::where('creator_id', $user->id)
                    ->orWhere('assigned_to', $user->id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            }

            return $query->orderBy('id', 'desc')->get();
        });

        return response()->json($tasks);
    }
    
    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'status' => 'pending',
            'creator_id' => auth()->id(),
        ]);

        Cache::forget("tasks_user_" . auth()->id());
        if ($request->assigned_to) {
            Cache::forget("tasks_user_{$request->assigned_to}");
        }

        return response()->json($task, 201);
    }

    /**
     * Display the specified task.
     */
    public function show(string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if (!$this->canAccessTask($task)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($task);
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if (!$this->canAccessTask($task)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|required|date',
            'status' => 'nullable|in:pending,completed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->only(['title', 'description', 'due_date', 'status', 'assigned_to']));

        Log::info('Task updated', [
            'task_id' => $task->id,
            'updated_by' => auth()->id(),
            'changes' => $request->only(['title', 'status', 'assigned_to', 'due_date']),
        ]);

        Cache::forget("tasks_user_{$task->creator_id}");
        if ($task->assigned_to) {
            Cache::forget("tasks_user_{$task->assigned_to}");
        }

        $this->clearAdminCache($request);

        return response()->json($task);
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(string $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if (auth()->user()->role !== 'admin' && $task->creator_id !== auth()->id()) {
            return response()->json(['error' => 'Only admin or task owner can delete'], 403);
        }

        $taskId = $task->id;
        $task->delete();

        Log::info('Task deleted', [
            'task_id' => $taskId,
            'deleted_by' => auth()->id(),
            'timestamp' => now()
        ]);

        Cache::forget("tasks_user_{$task->creator_id}");
        if ($task->assigned_to) {
            Cache::forget("tasks_user_{$task->assigned_to}");
        }

        return response()->json(['message' => 'Task deleted successfully']);
    }

    private function canAccessTask(Task $task): bool
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return true;
        }

        return $task->creator_id === $user->id || $task->assigned_to === $user->id;
    }

    private function clearAdminCache(Request $request = null): void
    {
        $admin = \App\Models\User::where('role', 'admin')->first();
        if ($admin) {
            Cache::forget("tasks_admin_all");

            // if ($request) {
            //     $key = "tasks_admin_filter_{$request->status}_search_{$request->search}";
            //     Cache::forget($key);
            // }
        }
    }

    private function getCacheKey($user, $request): string
    {
        if ($user->role === 'admin') {
            return "tasks_admin_all";
        }
        return "tasks_user_{$user->id}_filter_{$request->status}_search_{$request->search}";
    }
}