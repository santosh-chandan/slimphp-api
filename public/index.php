<?php
/**
 * Main Entry - index
 * Author: Santoshchandan.it@gmail.com
 */
use Slim\Factory\AppFactory;
use Psr\Container\ContainerInterface;
use Santoshchandan\TasksApi\Services\TaskService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../config/database.php';

// Use PHP-DI container for Slim 4 
$container = new \DI\Container();

// Add TaskService to the container
$container->set('taskService', function(ContainerInterface $container) {
    return new TaskService();
});

// Create Logger instance
$logger = new Logger('task_api');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/api.log', Logger::DEBUG));
$container->set('logger', $logger);

// Create App instance and associate it with the container
AppFactory::setContainer($container);
// Create App instance
$app = AppFactory::create();

// Add Middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Register the error middleware
// You can turn these to false in production for security.
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Handling 404 error using error middleware
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, 'handleNotFound');

// Register Routes
(require __DIR__ . '/../src/Routes/TaskRoutes.php')($app);

// Run the app
$app->run();

// 404 handler
function handleNotFound(
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
): Response {
    $response = new \Slim\Psr7\Response();
    $response->getBody()->write(json_encode([
        'error' => 'Route not found 😕',
    ]));
    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
}
