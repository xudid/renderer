<?php

namespace Renderer;

/**
 *Renvoie la réponse finale présenté à l'utilisateur
 */

//use function Http\Response\send;
use Psr\Http\Server\MiddlewareInterface;
use Ui\Widgets\Views\AppPage;

/**
 *
 */
class Renderer
{

    /**
     * @var AppPage $page ;
     */
    private $page = null;
    private string $path;
    private string $viewsDirectory;

    /**
     * Renderer constructor.
     */
    function __construct()
    {
        $this->page = new AppPage();
    }

    public function setAppPage(AppPage $appPage)
    {
        $this->page = $appPage;
    }

    public function setPath(string $path)
    {
        if (file_exists($path)) {
            $this->path = $path;
        }
    }

    public function setLang(string $lang)
    {
        $this->page->setLang($lang);
    }

    public function setPageTitle(string $title)
    {
        $this->page->setTitle($title);
    }

    public function importCss(...$cssPaths)
    {

        foreach ($cssPaths as $cssPath) {
            $this->page->importCss($cssPath);
        }
        return $this;

    }

    public function importMeta(...$metas)
    {
        $this->page->importMeta($metas);
    }

    public function importScript(...$scripts)
    {
        foreach ($scripts as $script) {
            $this->page->importScript($script);
        }
    }

    public function feedNavBarItems(...$items)
    {
        foreach ($items as $item) {
            $this->page->addNavBarItem($item);
        }
    }

    /**
     *
     * @param object $view
     * @param int $id
     * @param string $path
     * @return AppPage
     */
    public function renderAppPage($view, $id = null, $path = null)
    {
        //Must render html php
        //html and php can be integrate  in appPage or send as his  for ajax request by example
        $extension = false;
        if (is_string($view)) {
            $extension = $this->getViewExtension($view);
        }

        //PHP file must echo the contained view|| HTML
        if (is_string($extension) && (strcmp($extension, ".php") == 0 || strcmp($extension, ".html") == 0)) {

            ob_start();
            if (is_null($path)) {
                $path = "";
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





    public function addNavBarItem($type, $path, $display, $altdisplay, $displayside)
    {
        $this->navbaritems[] = [$type, $path, $display, $altdisplay, $displayside];
        $this->page->addNavBarItem($type, $path, $display, $altdisplay, $displayside);

    }



    /**
     * @param string $viewsDirectory
     */
    public function setViewsDirectory(string $viewsDirectory): self
    {
        $this->viewsDirectory = $viewsDirectory;
        return $this;
    }

    /**
     * @param $view
     * @return bool|string : return the extension string or false
     */
    private function getViewExtension($view)
    {
        $extension = false;
        if (preg_match("#.php$#", $view)) $extension = ".php";
        if (preg_match("#.html$#", $view)) $extension = ".html";
        return $extension;
    }
}
