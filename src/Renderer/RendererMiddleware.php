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

    public function addRenderingStrategy(string $name, RendererInterface $renderer)
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
        if ($extension == 'css') {
            $response->getBody()->write((string)$input);
            $handler->handle($request);
            return $response;
        }
        if ($extension) {
            $renderer = $this->renderingStrategies[$extension];
        } else {
            $renderer = new PageRenderer();
            $renderer->importCss($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'css/ui.css');
        }
        $output = $renderer->render($input);
        $request = $request->withAttribute($this->resultKey, $output);

        $response->getBody()->write((string)$output);
        $handler->handle($request);

        return $response;
    }
}
