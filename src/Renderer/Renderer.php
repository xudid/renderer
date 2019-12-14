<?php
namespace Renderer;

/**
 *Renvoie la réponse finale présenté à l'utilisateur
 */

//use function Http\Response\send;
use Psr\Container\ContainerInterface;
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
	 * @var array $urlMatrice
	 */
	private $urlMatrice = [];
	/**
	 * @var AppPage $page ;
	 */
	private $page = null;

	private $redirection = "";

	private $navbaritems = [];
	private array $scriptToHead = [];
	private array $scriptToEnd = [];
	private array $moduleInfos = [];
	/**
	 * @var ContainerInterface
	 */
	private ContainerInterface $container;
	private string $path;
	/**
	 * @var object $sidebar
	 */
	/**
	 * Return a Renderer instance
	 * @param ContainerInterface $container
	 */
	function __construct(ContainerInterface $container)
	{
		if ($container) {
			$this->container = $container;
		}
		if ($this->container->has('app_page_class')) {
			$this->page = (new $this->container->get('app_page_class'))($this->container);
		} elseif ($container->has('app_page')) {
			$page = $container->get('app_page');
		} else {
			$this->page = new AppPage();
		}

		if ($this->container->has('module_infos')) {
			$this->moduleInfos = $this->container->get('module_infos') ?? [];
		}

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

	public function importScript(...$scripts)
	{
		foreach ($scripts as $script) {
			$position = $script->getPosition();
			if ($script instanceof Script && strcmp($position, Script::SCRIPT_TO_END)) {
				$this->scriptToEnd[] = $script;

			} elseif ($script instanceof Script && strcmp($position, Script::SCRIPT_TO_HEAD)) {
				$this->scriptToHead[] = $script;
			}
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
				if ($this->container->has("views_directory") && file_exists($this->container->get("views_directory"). "/" . $view )) {
					$path = $this->container->get("views_directory"). "/" . $view;
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

			return $this->page;

		} else {

			$this->page->setContentView($view);

			return $this->page;
		}
	}

	/**
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $handler -
	 * @return ResponseInterface
	 */
	function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{

		$response = $handler->handle($request);
		if ($this->redirection == "") {
			$returnAppPage = $request->getAttribute("returnAppPage");
			if ($returnAppPage) {
				$response->getBody()->write($this->page);
			}
		} else {
			$response = $response->withHeader("Location", $this->redirection);
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

	public function redirectTo($location)
	{
		$this->redirection = $location;
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
