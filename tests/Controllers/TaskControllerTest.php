<?php

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Santoshchandan\TasksApi\Services\TaskService;
use Santoshchandan\TasksApi\Controllers\TaskController;
use Santoshchandan\TasksApi\Models\Task;

class TaskControllerTest extends TestCase
{
    private $taskServiceMock;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the TaskService
        $this->taskServiceMock = m::mock(TaskService::class);
        $this->controller = new TaskController($this->taskServiceMock);
    }

    protected function tearDown(): void
    {
        m::close(); // clean up mockery
    }

    /**
     * Test - Index returns tasks
     */
    public function testIndexReturnsTasks()
    {
        $tasks = [
            ['id' => 1, 'title' => 'Test Task 1'],
            ['id' => 2, 'title' => 'Test Task 2'],
        ];

        // Mock the service to return tasks
        $this->taskServiceMock
            ->shouldReceive('getTasks')
            ->with(1, 10) // default values
            ->once()
            ->andReturn($tasks);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/tasks');
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->index($request, $response);

        // Assert
        $this->assertEquals(200, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Test Task 1', $body);
        $this->assertStringContainsString('Test Task 2', $body);
    }

    /**
     * Test - Show returns task
     */
    public function testShowReturnsTask()
    {
        $task = ['id' => 1, 'title' => 'Test Task 1'];

        // Mock the service to return a specific task by ID
        $this->taskServiceMock
            ->shouldReceive('getTaskById')
            ->with(1)
            ->once()
            ->andReturn($task);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/tasks/1');
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->show($request, $response, ['id' => 1]);

        // Assert
        $this->assertEquals(200, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Test Task 1', $body);
    }

    /**
     * Test - Show returns not found
     */
    public function testShowReturnsNotFound()
    {
        // Mock the service to return null for non-existing task
        $this->taskServiceMock
            ->shouldReceive('getTaskById')
            ->with(99)
            ->once()
            ->andReturn(null);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('GET', '/tasks/99');
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->show($request, $response, ['id' => 99]);

        // Assert
        $this->assertEquals(404, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Task not found', $body);
    }

    /**
     * Test - Store creates task
     */
    public function testStoreCreatesTask()
    {
        $data = ['title' => 'Test Task 1'];
        $task = ['id' => 1, 'title' => 'Test Task 1'];

        // Mock the service to create the task
        $this->taskServiceMock
            ->shouldReceive('createTask')
            ->with($data)
            ->once()
            ->andReturn($task);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('POST', '/tasks')
            ->withParsedBody($data);
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->store($request, $response);

        // Assert
        $this->assertEquals(201, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Test Task 1', $body);
    }

    /**
     * Test - Store returns bad request if no title
     */
    public function testStoreReturnsBadRequestIfNoTitle()
    {
        $data = [];  // No title provided

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('POST', '/tasks')
            ->withParsedBody($data);
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->store($request, $response);

        // Assert
        $this->assertEquals(400, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Title is required', $body);
    }

    /**
     * Test - Update task
     */
    public function testUpdateTask()
    {
        // Create a real Task object instead of just an array
        $task = new Task();
        $task->id = 1;
        $task->title = 'Test Task 1';

        $updatedData = ['title' => 'Updated Task Title'];

        // Mock the service to return the Task object and update the task
        $this->taskServiceMock
            ->shouldReceive('getTaskById')
            ->with(1)
            ->once()
            ->andReturn($task);

        $this->taskServiceMock
            ->shouldReceive('updateTask')
            ->with($task, $updatedData)
            ->once()
            ->andReturn(true);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('PUT', '/tasks/1')
            ->withParsedBody($updatedData);
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->update($request, $response, ['id' => 1]);

        // Assert
        $this->assertEquals(204, $result->getStatusCode());
    }


    /**
     * Test - Destroy delete
     */
    public function testDestroyDeletesTask()
    {
        // Create a real Task object instead of just an array
        $task = new Task();
        $task->id = 1;
        $task->title = 'Test Task 1';

        // Mock the service to return the Task object and delete it
        $this->taskServiceMock
            ->shouldReceive('getTaskById')
            ->with(1)
            ->once()
            ->andReturn($task);

        $this->taskServiceMock
            ->shouldReceive('deleteTask')
            ->with($task)
            ->once()
            ->andReturn(true);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('DELETE', '/tasks/1');
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->destroy($request, $response, ['id' => 1]);

        // Assert
        $this->assertEquals(204, $result->getStatusCode());
    }

    /**
     * Test - Destroy returns not found
     */
    public function testDestroyReturnsNotFound()
    {
        // Mock the service to return null for non-existing task
        $this->taskServiceMock
            ->shouldReceive('getTaskById')
            ->with(99)
            ->once()
            ->andReturn(null);

        // Create PSR-7 request and response
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest('DELETE', '/tasks/99');
        $response = $factory->createResponse();

        // Call the controller method
        $result = $this->controller->destroy($request, $response, ['id' => 99]);

        // Assert
        $this->assertEquals(404, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('Task not found', $body);
    }
}
