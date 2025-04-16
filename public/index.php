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

// Load Eloquent ORM (Illuminate)
require __DIR__ . '/../config/database.php';

// Create Container using Psr\Container\ContainerInterface
$container = new \DI\Container();  // Use PHP-DI container for Slim 4
// Add TaskService to the container
$container->set('taskService', function(ContainerInterface $container) {
    return new TaskService();  // Assuming TaskService is in the right namespace
});

// Create Logger instance
$logger = new Logger('task_api');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/api.log', Logger::DEBUG));

// Add logger to the container
$container->set('logger', $logger);

// Create App instance and associate it with the container
AppFactory::setContainer($container);

// Create App instance
$app = AppFactory::create();

// Add Middlewares
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Register the error middleware
// Enables Slim's built-in error handling, which catches exceptions and returns proper error responses.
// Show detailed error messages | Log errors | Log detailed error info (stack trace etc.)
// You can turn these to false in production for security.
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Handling 404 error using error middleware
// if generating HttpNotFoundException then call 'handleNotFound'
$errorMiddleware->setErrorHandler(HttpNotFoundException::class, 'handleNotFound');

// Add TaskService to the container
$app->getContainer()->set('taskService', function() {
    return new TaskService();  // Assuming TaskService is in the right namespace
});

// Register Routes
(require __DIR__ . '/../src/Routes/TaskRoutes.php')($app);

// Run the app
$app->run();

// Separate function instead of closure
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
