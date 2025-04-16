<?php
/**
 * @OA\Info(
 *     title="Tasks API",
 *     version="1.0.0",
 *     description="Tasks API built with Slim 4"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Localhost server"
 * )
 */
namespace Santoshchandan\TasksApi\Controllers;

use Santoshchandan\TasksApi\Services\TaskService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nyholm\Psr7\Stream;
use Monolog\Logger;
use Exception;

class TaskController
{
    protected $taskService;
    protected $logger;

    public function __construct(
        TaskService $taskService,
        Logger $logger
    ){
        $this->taskService = $taskService;
        $this->logger = $logger;
    }

    /**
     * @OA\Get(
     *     path="/tasks",
     *     summary="Get all tasks",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $params = $request->getQueryParams();
            $page = (int)($params['page'] ?? 1);
            $limit = (int)($params['limit'] ?? 10);

            $tasks = $this->taskService->getTasks($page, $limit);

            $this->logger->info('Fetched ' . count($tasks) . ' tasks');
            // Convert the tasks array to JSON
            $response->getBody()->write(json_encode($tasks));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $this->logger->error('Error fetching tasks: ' . $e->getMessage());
            $error = json_encode(['error' => 'Failed to fetch tasks']);
            $stream = Stream::create($error);
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
    }

    /**
     * @OA\Get(
     *     path="/tasks/{id}",
     *     summary="Get a task by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task found"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function show(Request $request, Response $response, $args): Response
    {
        try {
            $task = $this->taskService->getTaskById($args['id']);

            if (!$task) {
                // Use Stream::create instead of fromString
                $stream = Stream::create(json_encode(['error' => 'Task not found']));
                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody($stream);
            }

            // Fix here: Use $task instead of $tasks
            $response->getBody()->write(json_encode($task));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $this->logger->error('Error fetching task: ' . $e->getMessage());
            $error = json_encode(['error' => 'Failed to fetch task']);
            $stream = Stream::create($error);
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (!isset($data['title']) || strlen($data['title']) < 3) {
                $error = json_encode([
                    'error' => 'Title is required and must be at least 3 characters.'
                ]);
                $stream = Stream::create($error);
                return $response
                    ->withStatus(400)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody($stream);
            }

            $task = $this->taskService->createTask($data);
            $response->getBody()->write(json_encode($task));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
            $this->logger->error('Error creating task: ' . $e->getMessage());
            $error = json_encode(['error' => 'Failed to create task']);
            $stream = Stream::create($error);
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
    }

    public function update(Request $request, Response $response, $args): Response
    {
        try {
            $task = $this->taskService->getTaskById($args['id']);
            if (!$task) {
                $stream = Stream::create(json_encode(['error' => 'Task not found']));
                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody($stream);
            }

            $data = $request->getParsedBody();
            // Pass the $task object, not the $data array
            $this->taskService->updateTask($task, $data);
            return $response->withStatus(204);
        } catch (Exception $e) {
            $this->logger->error('Error updating task: ' . $e->getMessage());
            $error = json_encode(['error' => 'Failed to update task']);
            $stream = Stream::create($error);
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
    }

    public function destroy(Request $request, Response $response, $args): Response
    {
        try {
            $task = $this->taskService->getTaskById($args['id']);
            if (!$task) {
                $stream = Stream::create(json_encode(['error' => 'Task not found']));
                return $response
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'application/json')
                    ->withBody($stream);
            }

            $this->taskService->deleteTask($task);
            return $response->withStatus(204);
        } catch (Exception $e) {
            $this->logger->error('Error deleting task: ' . $e->getMessage());
            $error = json_encode(['error' => 'Failed to delete task']);
            $stream = Stream::create($error);
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
    }
}
