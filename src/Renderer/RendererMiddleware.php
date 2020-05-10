<?php


namespace Renderer;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RendererMiddleware implements  MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler -
     * @return ResponseInterface
     */
    function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        $response = $handler->handle($request);
        $render = $request->getAttribute("render");
        if ($render) {
            $response->getBody()->write($this->page);
        }
        return $response;
    }


}