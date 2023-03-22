<?php

namespace Renderer;

interface RendererInterface
{
    public function render(mixed $content, array $options = []): string;
}
