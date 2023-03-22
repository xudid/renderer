<?php

namespace Renderer;

use Exception;

class JsonRenderer implements RendererInterface
{
    /**
     * @throws Exception
     */
    public function render(mixed $content, array $options = []): string
    {
        try {
            return json_encode($content, JSON_THROW_ON_ERROR |  JSON_PRETTY_PRINT);

        } catch (Exception $ex) {
            throw new Exception('Json renderer exception');
        }
    }
}
