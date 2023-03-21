<?php

namespace Renderer;

use DOMDocument;
use DOMElement;
use DOMException;
use Exception;

class ArrayToXmlRenderer
{
    private DOMDocument $xml;

    public function __construct()
    {
        $this->xml = new DOMDocument();
    }

    /**
     * @throws Exception
     */
    public function render(array $content, $options = []): string
    {
        try {
            $rootKey = $options['root'] ?? 'root';
            $domElement = $this->renderArray($rootKey, $content);
            $this->xml->appendChild($domElement);

            return $this->xml->saveXML();
        } catch (Exception $ex) {
            throw new Exception('ArrayToXml Render exception');
        }
    }

    /**
     * @throws Exception
     */
    private function renderArray(int|string $key, array $value): DOMElement
    {
        $tag = $this->xml->createElement($key);
        if ($tag === false) {
            throw new Exception();
        }

        foreach ($value as $k => $item) {
            if (is_string($item)) {
                $subElement = $this->renderString($k, $item);
            } elseif (is_array($item)) {
                $subElement = $this->renderArray($k, $item);
            } else {
                throw new Exception();
            }

            $tag->appendChild($subElement);
        }

        return $tag;
    }

    /**
     * @throws Exception
     */
    private function renderString(int|string $key, $value): DOMElement
    {
        $tag = $this->xml->createElement($key);
        if ($tag === false) {
            throw new Exception();
        }

        $valueElement = $this->xml->createTextNode($value);
        if ($valueElement === false) {
            throw new Exception();
        }

        $tag->appendChild($valueElement);

        return $tag;
    }
}