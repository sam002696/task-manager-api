<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use App\Models\User;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $taskServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Fully mocking the TaskService class
        $this->taskServiceMock = \Mockery::mock(TaskService::class);

        // Binding the mock instance to Laravel's service container
        $this->app->instance(TaskService::class, $this->taskServiceMock);
    }




    /**
     * Test getting all tasks.
     */
    public function test_can_get_all_tasks()
    {
        // Creating a fake user
        $user = User::factory()->create();

        // Faking data for the response
        $tasksData = [
            'tasks' => [
                ['id' => 1, 'name' => 'Task 1', 'status' => 'To Do'],
                ['id' => 2, 'name' => 'Task 2', 'status' => 'In Progress'],
            ],
            'pagination' => ['total' => 2, 'current_page' => 1, 'per_page' => 10]
        ];

        // Mocking the TaskService's getAllTasks method
        $this->taskServiceMock
            ->shouldReceive('getAllTasks')
            ->once()
            ->andReturn($tasksData);

        // Act - Making a GET request to the /api/tasks endpoint
        $response = $this->actingAs($user)->getJson('/api/tasks');

        // Asserting the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tasks retrieved successfully',
                'data' => $tasksData['tasks'],
                'meta' => $tasksData['pagination'],
            ]);
    }

    /**
     * Test creating a task.
     */
    public function test_can_create_task()
    {
        $user = User::factory()->create();

        $taskData = [
            'name' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'To Do',
            'due_date' => now()->toDateString(),
        ];

        // Mocking the TaskService's createTask method
        $this->taskServiceMock
            ->shouldReceive('createTask')
            ->once()
            ->andReturnUsing(function () use ($taskData) {
                return $taskData;
            });

        // Act
        $response = $this->actingAs($user)->postJson('/api/tasks', $taskData);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Task created successfully',
                'data' => $taskData,
            ]);
    }

    /**
     * Test showing a task.
     */
    public function test_can_show_task()
    {
        $user = User::factory()->create();
        $task = ['id' => 1, 'name' => 'Task 1', 'status' => 'To Do'];

        // Mocking the TaskService's getTaskById method
        $this->taskServiceMock
            ->shouldReceive('getTaskById')
            ->with(1)
            ->once()
            ->andReturnUsing(function () use ($task) {
                return $task;
            });

        // Act - Making a GET request to the /api/tasks/1 endpoint
        $response = $this->actingAs($user)->getJson('/api/tasks/1');

        // Asserting the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Task retrieved successfully',
                'data' => $task,
            ]);
    }

    /**
     * Testing updating a task.
     */
    public function test_can_update_task()
    {
        $user = User::factory()->create();
        $taskUpdateData = ['name' => 'Updated Task', 'status' => 'In Progress'];

        // Mocking the TaskService's updateTask method
        $this->taskServiceMock
            ->shouldReceive('updateTask')
            ->withAnyArgs()
            ->once()
            ->andReturnUsing(function () use ($taskUpdateData) {
                return $taskUpdateData;
            });

        // Act - Making a PUT request to the /api/tasks/1 endpoint
        $response = $this->actingAs($user)->putJson('/api/tasks/1', $taskUpdateData);

        // Asserting the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Task updated successfully',
                'data' => $taskUpdateData,
            ]);
    }

    /**
     * Test deleting a task.
     */
    public function test_can_delete_task()
    {
        $user = User::factory()->create();

        // Mocking the TaskService's deleteTask method
        $this->taskServiceMock
            ->shouldReceive('deleteTask')
            ->with(1)
            ->once()
            ->andReturn(true);

        // Act - Making a DELETE request to the /api/tasks/1 endpoint
        $response = $this->actingAs($user)->deleteJson('/api/tasks/1');

        // Asserting the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Task deleted successfully',
            ]);
    }

    /**
     * Cleaning up Mockery after tests.
     */
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
