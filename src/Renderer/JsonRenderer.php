<?php

namespace Renderer;

use Exception;

class JsonRenderer
{
    /**
     * @throws Exception
     */
    public function render($content, $options = [])
    {
        try {
            return json_encode($content, JSON_THROW_ON_ERROR |  JSON_PRETTY_PRINT);

        } catch (Exception $ex) {
            throw new Exception('Json renderer exception');
        }
    }
}