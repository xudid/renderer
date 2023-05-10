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

        $renderer = new PageRenderer();
        $this->renderingStrategies['page'] = $renderer;
    }

    public function addRenderingStrategy(string $name, RendererInterface $renderer)
    {
        $this->renderingStrategies[$name] = $renderer;
    }

    public function setPageRenderer(RendererInterface $renderer)
    {
        $this->renderingStrategies['page'] = $renderer;
    }

    public function getPageRenderer(): RendererInterface
    {
        return $this->renderingStrategies['page'];
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
            $strategy = $extension;
        } else {
            $strategy = 'page';
        }

        $renderer = $this->renderingStrategies[$strategy];
        $output = $renderer->render($input);
        $request = $request->withAttribute($this->resultKey, $output);

        $response->getBody()->write((string)$output);
        $handler->handle($request);

        return $response;
    }
}
