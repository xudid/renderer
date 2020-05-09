<?php
namespace Renderer;

/**
 *Renvoie la réponse finale présenté à l'utilisateur
 */

//use function Http\Response\send;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ui\HTML\Elements\Nested\Script;
use Ui\Widgets\Views\AppPage;

/**
 *
 */
class Renderer implements MiddlewareInterface
{

	/**
	 * @var AppPage $page ;
	 */
	private $page = null;

	private $redirection = "";

	private $navbaritems = [];
	private array $scriptToHead = [];
	private array $scriptToEnd = [];
	private array $moduleInfos = [];


	private string $path;
    private string $viewsDirectory;
    /**
	 * @var object $sidebar
	 */
	/**
	 * Return a Renderer instance
	 * @param ContainerInterface $container
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
			$this->page->addScript($script);
		}
	}

	/**
	 *
	 * @param object $view
	 * @param int $id
	 * @param string $path
	 * @return GuzzleHttp\Psr7\Response;
	 */
	public function renderAppPage($view, $id = null, $path = null)
	{
		//Must render html php
		//html and php can be integrate  in appPage or send as his  for ajax request by example

		$extension = $this->getViewExtension($view);

		//PHP file must echo the contained view|| HTML
		if (is_string($extension) && (strcmp($extension, ".php") == 0 || strcmp($extension, ".html") == 0)) {

			ob_start();
			if (is_null($path)) {
				$path = "";
				if (strlen($this->viewsDirectory) > 0 && file_exists($this->viewsDirectory. "/" . $view )) {
					$path = $this->viewsDirectory. "/" . $view;
				}

				if (file_exists($this->path. "/" . $view)) {
					$path = $this->path. "/" . $view;
				}

				include $path ;
			} else {
				require $path . "/" . $view;
			}
			$content = ob_get_clean();
			$this->page->setContentView($content);
		} else {

			$this->page->setContentView($view);
		}
        return $this->page;
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler -
	 * @return ResponseInterface
	 */
	function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{

		$response = $handler->handle($request);
		if (strlen($this->redirection) >0) {
            $response = $response->withHeader("Location", $this->redirection);
            return  $response;
        }

        $render = $request->getAttribute("render");
        if ($render) {
            $response->getBody()->write($this->page);
        }
		return $response;
	}

	/*
	* @param array $urls : an array that contains
	* navigable urls from current url
	*/
	public function renderSideBar(array $urls)
	{


		if (key_exists("GET", $urls)) {
			foreach ($urls["GET"] as $key => $map) {
				foreach ($map as $url => $display) {

					$this->page->addSideBarItem("$display ", "$url");
				}

			}
		}
		if (key_exists("POST", $urls)) {
			foreach ($urls["POST"] as $key => $map) {
				foreach ($map as $url => $display) {

					$this->page->addSideBarItem("$display ", "$url");
				}

			}
		}
	}


	public function addNavBarItem($type, $path, $display, $altdisplay, $displayside)
	{
		$this->navbaritems[] = [$type, $path, $display, $altdisplay, $displayside];
		$this->page->addNavBarItem($type, $path, $display, $altdisplay, $displayside);

	}

    /**
     * @param array $moduleInfos
     */
    public function setModuleInfos(array $moduleInfos): self
    {
        $this->moduleInfos = $moduleInfos;
        return $this;
    }

    /**
     * @param string $viewsDirectory
     */
    public function setViewsDirectory(string $viewsDirectory): self
    {
        $this->viewsDirectory = $viewsDirectory;
        return $this;
    }



	public function redirectTo($location)
	{
		$this->redirection = $location;
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
