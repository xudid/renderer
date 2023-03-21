<?php

namespace Renderer;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RendererMiddleware implements MiddlewareInterface
{
    private string $routeKey = 'route';
    private string $resultKey = 'result';
    private array $renderingStrategies = [];

    public function __construct(string $routeKey = '', string $resultKey = '')
    {
        if ($resultKey) {
            $this->resultKey = $resultKey;
        }

        if ($routeKey) {
            $this->routeKey = $routeKey;
        }
    }

    public function addRenderingStrategy(string $name, $renderer)
    {
        $this->renderingStrategies[$name] = $renderer;
    }
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute($this->routeKey);
        $response = $handler->handle($request);

        $input = $request->getAttribute($this->resultKey);
        if (!$input) {
            return $response;
        }
        // Todo implement getPrefferedAcceptHeader use it to get the right renderer
        $extension = $route->getExtension();
        if ($extension) {
            $renderer = $this->renderingStrategies[$extension];
        } else {
            $renderer = new PageRenderer();
        }

        $output = $renderer->render($input);
        $request = $request->withAttribute($this->resultKey, $output);

        $response->getBody()->write((string)$output);
        $handler->handle($request);

        return $response;
    }
}
