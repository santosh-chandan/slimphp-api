<?php
/**
 * Task API Auth Middleware
 * Author: Santoshchandan.it@gmail.com
*/

namespace Santoshchandan\TasksApi\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(Request $request, Handler $handler): Response
    {
        // Dummy auth: expects a header `Authorization: Bearer $_ENV['API_KEY']`
        $secret = $_ENV['API_KEY'];
        $authHeader = $request->getHeaderLine('Authorization');

        if (strpos($authHeader, 'Bearer ') !== 0 || trim(substr($authHeader, 7)) !== $secret) {
            $response = new SlimResponse();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Auth success → continue to controller
        return $handler->handle($request);
    }
}
