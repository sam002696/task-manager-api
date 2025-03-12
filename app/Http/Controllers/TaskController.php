<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Displaying a list of tasks for the authenticated user with search and filtering.
     */
    public function index(Request $request)
    {
        //  Only retrieving tasks belonging to the authenticated user
        $query = Task::where('user_id', Auth::id());

        //  Searching by name
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        //  Filtering by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        //  Filtering by due date range
        if ($request->filled('due_date_from') && $request->filled('due_date_to')) {
            $query->whereBetween('due_date', [$request->due_date_from, $request->due_date_to]);
        }

        //  Sorting results (Default: Latest first)
        $tasks = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'data' => $tasks->items(),
            'status' => 'success',
            'message' => 'Tasks retrieved successfully',
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => $tasks->total(),
                'total_pages' => $tasks->lastPage(),
                'has_more_pages' => $tasks->hasMorePages(),
            ]
        ]);
    }


    /**
     * Storing a new task for the authenticated user.
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:To Do,In Progress,Done',
                'due_date' => 'nullable|date',
            ]);

            $task = Task::create([
                'user_id' => Auth::id(), //  Assign task to the authenticated user
                'name' => $validated['name'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'due_date' => $validated['due_date'],
            ]);

            return response()->json([
                'data' => $task,
                'status' => 'success',
                'message' => 'Task created successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            //  Extract First Validation Error Message
            $errors = $e->errors();
            $firstErrorMessage = collect($errors)->first()[0];

            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => $firstErrorMessage, // Show first error message
                'errors' => $errors // Full error details
            ], 422);
        } catch (\Exception $e) {
            //  Handle Other Unexpected Errors
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a single task (only if the authenticated user owns it).
     */
    public function show($id)
    {
        //  Finding the task
        $task = Task::find($id);

        //  If task does not exist, returning 404 Not Found
        if (!$task) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Task not found',
                'errors' => null
            ], 404);
        }

        //  If task exists but does not belong to the authenticated user, returning 403 Forbidden
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Unauthorized access',
                'errors' => null
            ], 403);
        }

        //  Returning task data
        return response()->json([
            'data' => $task,
            'status' => 'success',
            'message' => 'Task retrieved successfully',
            'errors' => null
        ]);
    }


    /**
     * Updating an existing task (only if the authenticated user owns it).
     */
    public function update(Request $request, $id)
    {

        try {
            $task = Task::where('id', $id)->where('user_id', Auth::id())->first();


            if (!$task) {
                return response()->json([
                    'data' => null,
                    'status' => 'error',
                    'message' => 'Unauthorized access or task not found',
                    'errors' => null
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:To Do,In Progress,Done',
                'due_date' => 'nullable|date',
            ]);

            $task->update($validated);

            return response()->json([
                'data' => $task,
                'status' => 'success',
                'message' => 'Task updated successfully',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            //  Extracting First Validation Error Message
            $errors = $e->errors();
            $firstErrorMessage = collect($errors)->first()[0];

            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => $firstErrorMessage, // Showing first error message
                'errors' => $errors // Full validation errors
            ], 422);
        } catch (\Exception $e) {
            //  Handle Unexpected Errors
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deleting a task (only if the authenticated user owns it).
     */
    public function destroy($id)
    {
        try {
            //  Finding the task
            $task = Task::find($id);

            //  If task does not exist, returning 404 Not Found
            if (!$task) {
                return response()->json([
                    'data' => null,
                    'status' => 'error',
                    'message' => 'Task not found',
                    'errors' => null
                ], 404);
            }

            //  If task exists but does not belong to the authenticated user, returning 403 Forbidden
            if ($task->user_id !== Auth::id()) {
                return response()->json([
                    'data' => null,
                    'status' => 'error',
                    'message' => 'Unauthorized access',
                    'errors' => null
                ], 403);
            }

            //  Deleting Task
            $task->delete();

            return response()->json([
                'data' => null,
                'status' => 'success',
                'message' => 'Task deleted successfully',
                'errors' => null
            ]);
        } catch (\Exception $e) {
            //  Handling Unexpected Errors
            return response()->json([
                'data' => null,
                'status' => 'error',
                'message' => 'Something went wrong',
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
