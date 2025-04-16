<?php
/**
 * Task API Routes
 * Author: Santoshchandan.it@gmail.com
 */

use Slim\App;
use Santoshchandan\TasksApi\Controllers\TaskController;
use Santoshchandan\TasksApi\Middleware\AuthMiddleware;

return function (App $app) {
    $taskController = new TaskController(
        $app->getContainer()->get('taskService'),
        $app->getContainer()->get('logger')
    );
    $auth = new AuthMiddleware();
    
    /**
     * Main Route
     */
    $app->get('/', function ($request, $response) {
        $response->getBody()->write('Welcome to Tasks API');
        return $response;
    });

    /**
     * Swagger-OpenAPI
     */
    $app->get('/docs/openapi.json', function ($request, $response) {
        $openapi = \OpenApi\Generator::scan([
            realpath(__DIR__ . '/../Controllers'),
            realpath(__DIR__ . '/../../docs'),
        ]);
    
        $response->getBody()->write(json_encode($openapi));
        return $response->withHeader('Content-Type', 'application/json');
    });    

    /**
     * None Auth Routes
     */
    $app->get('/tasks', [$taskController, 'index']);
    $app->get('/tasks/{id}', [$taskController, 'show']);

    /**
     * Auth Routes
     */
    $app->post('/tasks', [$taskController, 'store'])->add($auth);
    $app->put('/tasks/{id}', [$taskController, 'update'])->add($auth);
    $app->delete('/tasks/{id}', [$taskController, 'destroy'])->add($auth);
};
