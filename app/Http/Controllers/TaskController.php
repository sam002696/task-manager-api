<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Services\ApiResponseService;
use Illuminate\Validation\ValidationException;
use Exception;


class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Get all tasks with filtering.
     */
    public function index(Request $request)
    {
        $tasksData = $this->taskService->getAllTasks($request);

        return ApiResponseService::successResponse(
            $tasksData['tasks'],
            'Tasks retrieved successfully',
            200,
            $tasksData['pagination']
        );
    }

    /**
     * Store a new task.
     */
    public function store(Request $request)
    {
        try {
            $task = $this->taskService->createTask($request);

            return ApiResponseService::successResponse(
                $task,
                'Task created successfully',
                201
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    /**
     * Show a single task.
     */
    public function show($id)
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return ApiResponseService::errorResponse('Task not found or unauthorized access', 404);
        }

        return ApiResponseService::successResponse(
            $task,
            'Task retrieved successfully'
        );
    }

    /**
     * Update an existing task.
     */
    public function update(Request $request, $id)
    {
        try {
            $task = $this->taskService->updateTask($request, $id);

            if (!$task) {
                return ApiResponseService::errorResponse('Task not found or unauthorized access', 403);
            }

            return ApiResponseService::successResponse(
                $task,
                'Task updated successfully'
            );
        } catch (ValidationException $e) {
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    /**
     * Delete a task.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->taskService->deleteTask($id);

            if (!$deleted) {
                return ApiResponseService::errorResponse('Task not found or unauthorized access', 403);
            }

            return ApiResponseService::successResponse(
                null,
                'Task deleted successfully'
            );
        } catch (Exception $e) {
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
