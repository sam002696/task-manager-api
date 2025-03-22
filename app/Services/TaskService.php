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
     * Retrieving all tasks for the authenticated user with filters.
     */
    public function getAllTasks($request)
    {
        // Cache Key
        $userId = Auth::id();
        $cacheKey = "tasks_{$userId}";

        // Retrieve tasks from cache if available
        return Cache::remember($cacheKey, now()->addMinutes(0), function () use ($request, $userId) {
            $query = Task::where('user_id', $userId);

            // Appling Filters
            if ($request->filled('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            // Filtering by due date range
            if ($request->filled('due_date_from') && $request->filled('due_date_to')) {
                $query->whereBetween('due_date', [
                    Carbon::parse($request->due_date_from)->startOfDay(),
                    Carbon::parse($request->due_date_to)->endOfDay()
                ]);
            }

            // Sorting
            $sortOrder = $request->get('sort', 'desc'); // Default: Newest First
            $query->orderBy('created_at', $sortOrder);

            // Paginating
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
     * Creating a new task.
     */
    public function createTask($request)
    {
        // Validating incoming request
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:To Do,In Progress,Done',
            'due_date' => 'nullable|date',
        ])->validate();

        // Creating task
        $task = Task::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
        ]);

        // Clearing cache
        Cache::forget("tasks_" . Auth::id());

        return $task;
    }

    /**
     * Retrieving a single task, ensuring ownership.
     */
    public function getTaskById($id)
    {
        return Task::where('id', $id)->where('user_id', Auth::id())->first();
    }

    /**
     * Updating a task.
     */
    public function updateTask($request, $id)
    {
        // Retrieving task
        $task = $this->getTaskById($id);

        if (!$task) {
            return null;
        }

        // Validating incoming request
        $validated = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:To Do,In Progress,Done',
            'due_date' => 'nullable|date',
        ])->validate();

        // Updating task
        $task->update($validated);
        Cache::forget("tasks_" . Auth::id());

        return $task;
    }

    /**
     * Deleting a task.
     */
    public function deleteTask($id)
    {
        // Retrieving task
        $task = $this->getTaskById($id);

        // Deleting task
        if (!$task) {
            return null;
        }

        // Clearing cache
        $task->delete();
        Cache::forget("tasks_" . Auth::id());

        return true;
    }
}
