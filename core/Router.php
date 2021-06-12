<?php

namespace app\core;

// use app\core\Request;
// use app\core\Response;

class Router
{

  public Request $request;
  public Response $response;
  protected array $routes = [];

  /**
   * Router Constructor
   *
   * @param \app\core\Request $request
   * @param \app\core\Response $response
   */
  public function __construct(Request $request, Response $response)
  {
    $this->request = $request;
    $this->response = $response;
  }

  public function get($path, $callback)
  {
    $this->routes['get'][$path] = $callback;
  }

  public function post($path, $callback)
  {
    $this->routes['post'][$path] = $callback;
  }

  public function resolve()
  {
    $path = $this->request->getPath();
    $method = $this->request->getMethod();
    $callback = $this->routes[$method][$path] ?? false;

    if($callback === false) {
      $this->response->setStatusCode(404);
      return $this->renderContent("No route found");
    }
    if(is_string($callback)) {
      return $this->renderView($callback);
    }
    return call_user_func($callback);
  }

  public function renderView($view, $params = [])
  {
    $layoutContent = $this->layoutContent();
    $viewContent = $this->renderOnlyView($view, $params);
    return str_replace('{{content}}', $viewContent, $layoutContent);
  }

  public function renderContent($content)
  {
    $layoutContent = $this->layoutContent();
    return str_replace('{{content}}', $content, $layoutContent);
  }

  protected function layoutContent()
  {
    ob_start();
    include_once Application::$ROOT_DIR."/views/layouts/main.php";
    return ob_get_clean();
  }

  protected function renderOnlyView($view, $params = [])
  {
    foreach ($params as $key => $value) {
      $$key = $value;
    }

    ob_start();
    include_once Application::$ROOT_DIR."/views/$view.php";
    return ob_get_clean();
  }
}
