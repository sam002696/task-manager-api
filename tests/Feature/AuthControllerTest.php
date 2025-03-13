<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $authServiceMock;

    public function setUp(): void
    {
        parent::setUp();

        // Mocking AuthService
        $this->authServiceMock = Mockery::mock(AuthService::class);
        $this->app->instance(AuthService::class, $this->authServiceMock);
    }

    /**
     * Testing user registration.
     */
    public function test_can_register_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        // Mocking the AuthService's registerUser method
        $this->authServiceMock
            ->shouldReceive('registerUser')
            ->once()
            ->andReturn((object) $userData); // Simulating User Object

        // Act - Making a POST request to the /api/register endpoint
        $response = $this->postJson('/api/register', $userData);

        // Asserting the response
        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => ['user' => $userData],
            ]);
    }

    /**
     * Test login with valid credentials.
     */
    public function test_can_login_user()
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $loginData = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        $authData = [
            'user' => $user->toArray(), // Converting User model to array
            'token' => 'sample_token_123',
        ];

        // Mocking the AuthService's loginUser method
        $this->authServiceMock
            ->shouldReceive('loginUser')
            ->once()
            ->andReturn($authData);

        // Act - Making a POST request to the /api/login endpoint
        $response = $this->postJson('/api/login', $loginData);

        //  Asserting the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $authData['user'], // Ensure we match the array format
                    'token' => $authData['token'],
                ],
            ]);
    }


    /**
     * Testing login with invalid credentials.
     */
    public function test_cannot_login_with_invalid_credentials()
    {
        $loginData = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ];

        // Mocking the AuthService's loginUser method to return null
        $this->authServiceMock
            ->shouldReceive('loginUser')
            ->once()
            ->andReturn(null);

        // Act - Making a POST request to the /api/login endpoint
        $response = $this->postJson('/api/login', $loginData);

        // Asserting the response
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ]);
    }

    /**
     * Test getting authenticated user.
     */
    public function test_can_get_authenticated_user()
    {
        $user = User::factory()->create();

        // Act - Making a GET request to the /api/user endpoint
        $response = $this->actingAs($user)->getJson('/api/user');

        // Asserting the response
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User data retrieved',
                'data' => ['user' => ['id' => $user->id, 'email' => $user->email]],
            ]);
    }

    /**
     * Test getting user without authentication.
     */
    public function test_cannot_get_user_when_not_authenticated()
    {
        // Act - Making a GET request to the /api/user endpoint without authentication
        $response = $this->getJson('/api/user');

        // Asserting the response
        $response->assertStatus(401);
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
