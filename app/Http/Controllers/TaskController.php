<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Services\ApiResponseService;
use Illuminate\Validation\ValidationException;
use Exception;


class TaskController extends Controller
{
    // Injecting the TaskService into the controller.
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Getting all tasks with filtering.
     */
    public function index(Request $request)
    {
        // Delegating task retrieval logic to TaskService
        $tasksData = $this->taskService->getAllTasks($request);

        // Returning a structured success response
        return ApiResponseService::successResponse(
            $tasksData['tasks'],
            'Tasks retrieved successfully',
            200,
            $tasksData['pagination']
        );
    }

    /**
     * Storing a new task.
     */
    public function store(Request $request)
    {
        try {
            // Delegating task creation logic to TaskService
            $task = $this->taskService->createTask($request);

            // Returning a structured success response
            return ApiResponseService::successResponse(
                $task,
                'Task created successfully',
                201
            );
        } catch (ValidationException $e) {
            // Handling validation errors
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            // Handling unexpected errors
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    /**
     * Showing a single task.
     */
    public function show($id)
    {
        // Delegating task retrieval logic to TaskService
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            // Handling task not found
            return ApiResponseService::errorResponse('Task not found or unauthorized access', 404);
        }

        // Returning a structured success response
        return ApiResponseService::successResponse(
            $task,
            'Task retrieved successfully'
        );
    }

    /**
     * Updating an existing task.
     */
    public function update(Request $request, $id)
    {
        try {
            // Delegating task update logic to TaskService
            $task = $this->taskService->updateTask($request, $id);

            if (!$task) {
                // Handling task not found
                return ApiResponseService::errorResponse('Task not found or unauthorized access', 403);
            }

            // Returning a structured success response
            return ApiResponseService::successResponse(
                $task,
                'Task updated successfully'
            );
        } catch (ValidationException $e) {
            // Handling validation errors
            return ApiResponseService::handleValidationError($e);
        } catch (Exception $e) {
            // Handling unexpected errors
            return ApiResponseService::handleUnexpectedError($e);
        }
    }

    /**
     * Deleting a task.
     */
    public function destroy($id)
    {
        try {
            // Delegating task deletion logic to TaskService
            $deleted = $this->taskService->deleteTask($id);

            if (!$deleted) {
                // Handling task not found
                return ApiResponseService::errorResponse('Task not found or unauthorized access', 403);
            }

            // Returning a structured success response
            return ApiResponseService::successResponse(
                null,
                'Task deleted successfully'
            );
        } catch (Exception $e) {
            // Handling unexpected errors
            return ApiResponseService::handleUnexpectedError($e);
        }
    }
}
