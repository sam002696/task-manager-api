<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TaskService
{
    /**
     * Retrieve all tasks for the authenticated user with filters.
     */
    public function getAllTasks($request)
    {
        $userId = Auth::id();
        $cacheKey = "tasks_{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(0), function () use ($request, $userId) {
            $query = Task::where('user_id', $userId);

            // Apply Filters
            if ($request->filled('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('due_date_from') && $request->filled('due_date_to')) {
                $query->whereBetween('due_date', [
                    Carbon::parse($request->due_date_from)->startOfDay(),
                    Carbon::parse($request->due_date_to)->endOfDay()
                ]);
            }

            $sortOrder = $request->get('sort', 'desc'); // Default: Newest First
            $query->orderBy('created_at', $sortOrder);

            $tasks = $query->paginate(10);

            return [
                'tasks' => $tasks->items(),
                'pagination' => [
                    'current_page' => $tasks->currentPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                    'total_pages' => $tasks->lastPage(),
                    'has_more_pages' => $tasks->hasMorePages(),
                ]
            ];
        });
    }

    /**
     * Create a new task.
     */
    public function createTask($request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:To Do,In Progress,Done',
            'due_date' => 'nullable|date',
        ])->validate();

        $task = Task::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        Cache::forget("tasks_" . Auth::id());

        return $task;
    }

    /**
     * Retrieve a single task, ensuring ownership.
     */
    public function getTaskById($id)
    {
        return Task::where('id', $id)->where('user_id', Auth::id())->first();
    }

    /**
     * Update a task.
     */
    public function updateTask($request, $id)
    {
        $task = $this->getTaskById($id);

        if (!$task) {
            return null;
        }

        $validated = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:To Do,In Progress,Done',
            'due_date' => 'nullable|date',
        ])->validate();

        $task->update($validated);
        Cache::forget("tasks_" . Auth::id());

        return $task;
    }

    /**
     * Delete a task.
     */
    public function deleteTask($id)
    {
        $task = $this->getTaskById($id);

        if (!$task) {
            return null;
        }

        $task->delete();
        Cache::forget("tasks_" . Auth::id());

        return true;
    }
}
