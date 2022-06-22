<?php

namespace Renderer;

use Ui\Widget\View\AppPage;

class Renderer
{
    protected AppPage $page;
    protected string $path;
    protected string $viewsDirectory;

    public function __construct()
    {
        $this->page = new AppPage();
    }

    public function setAppPage(AppPage $appPage): static
    {
        $this->page = $appPage;
        return $this;
    }

    public function setPath(string $path): static
    {
        if (file_exists($path)) {
            $this->path = $path;
        }
        return $this;
    }

    public function setLang(string $lang): static
    {
        $this->page->setLang($lang);
        return $this;
    }

    public function setPageTitle(string $title): static
    {
        $this->page->setTitle($title);
        return $this;
    }

    public function importCss(...$cssPaths): static
    {
        foreach ($cssPaths as $cssPath) {
            $this->page->importCss($cssPath);
        }

        return $this;
    }

    public function importMeta(...$metas): static
    {
        $this->page->importMeta($metas);
        return $this;
    }

    public function importScript(...$scripts): static
    {
        foreach ($scripts as $script) {
            $this->page->importScript($script);
        }

        return $this;
    }

    public function feedNavBarItems(...$items): static
    {
        foreach ($items as $item) {
            $this->page->addNavBarItem($item);
        }

        return $this;
    }

    public function renderAppPage($view, $id = null, $path = null)
    {
        //Must render html php
        //html and php can be integrate  in appPage or send as his  for ajax request by example
        $extension = false;
        if (is_string($view)) {
            $extension = $this->getViewExtension($view);
        }

        //PHP file must echo the contained view|| HTML
        if ($extension && (strcmp($extension, ".php") == 0 || strcmp($extension, ".html") == 0)) {

            ob_start();
            if (is_null($path)) {
                $path = '';
                if (strlen($this->viewsDirectory) > 0 && file_exists($this->viewsDirectory . DIRECTORY_SEPARATOR . $view)) {
                    $path = $this->viewsDirectory . DIRECTORY_SEPARATOR . $view;
                }

                if (file_exists($this->path . DIRECTORY_SEPARATOR . $view)) {
                    $path = $this->path . DIRECTORY_SEPARATOR . $view;
                }

                include $path;
            } else {
                require $path . DIRECTORY_SEPARATOR . $view;
            }
            $content = ob_get_clean();
            $this->page->setContentView($content);
        } else {
            $this->page->setContentView($view);
        }
        return $this->page;
    }

    public function addNavBarItem($type, $path, $display, $altdisplay, $displayside): static
    {
        $this->navbaritems[] = [$type, $path, $display, $altdisplay, $displayside];
        $this->page->addNavBarItem($type, $path, $display, $altdisplay, $displayside);

        return $this;
    }

    public function setViewsDirectory(string $viewsDirectory): static
    {
        $this->viewsDirectory = $viewsDirectory;
        return $this;
    }

    private function getViewExtension($view): string
    {
        $extension = '';
        if (preg_match("#.php$#", $view)) $extension = ".php";
        if (preg_match("#.html$#", $view)) $extension = ".html";
        return $extension;
    }
}
