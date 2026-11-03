<?php

namespace Source\Core;

use Source\Core\SimpleResponse;
use Source\Core\SimpleController;
use Source\Core\SimpleSession;

class SimpleRouter
{
  private array $routes = [];
  private array $namedRoutes = [];
  private array $groupStack = [];
  private string $baseNamespace = "Source\\Controller\\";

  public function __construct()
  {
    $this->groupStack[] = (object) [
      'prefix' => '',
      'module' => '',
      'controller' => '',
      'namePrefix' => ''
    ];
  }

  public function add(string $method, string $path, string $module, string $controller, string $action, ?string $name = null): void
  {
    $method = strtoupper($method);
    $path = $this->normalizePath($path);
    $params = $this->extractParamNames($path);
    $name = $name ?: $action;

    $route = (object) [
      'method' => $method,
      'path' => $path,
      'module' => $module,
      'controller' => $controller,
      'action' => $action,
      'name' => $name,
      'regex' => $this->pathToRegex($path, !empty($params)),
      'params' => $params
    ];

    $this->routes[] = $route;

    if (!empty($name)) {
      if (isset($this->namedRoutes[$name])) {
        throw new \Exception("Rota nomeada duplicada: {$name}");
      }

      $this->namedRoutes[$name] = $route;
    }
  }

  public function addGet(string $path, string $module, string $controller, string $action, ?string $name = null): void
  {
    $this->add('GET', $path, $module, $controller, $action, $name);
  }

  public function addPost(string $path, string $module, string $controller, string $action, ?string $name = null): void
  {
    $this->add('POST', $path, $module, $controller, $action, $name);
  }

  public function get(string $path, ?string $action = null, ?string $name = null, ?string $controller = null): void
  {
    $this->map('GET', $path, $action, $name, $controller);
  }

  public function post(string $path, ?string $action = null, ?string $name = null, ?string $controller = null): void
  {
    $this->map('POST', $path, $action, $name, $controller);
  }

  public function map(string $method, string $path, ?string $action = null, ?string $name = null, ?string $controller = null): void
  {
    $group = $this->currentGroup();

    $module = $group->module;
    $controller = $controller ?: $group->controller;
    $action = $action ?: $this->inferAction($path);
    $name = $name ?: $action;

    if (empty($module)) {
      throw new \Exception("Módulo não definido para a rota {$path}");
    }

    if (empty($controller)) {
      throw new \Exception("Controlador não definido para a rota {$path}");
    }

    if (empty($action)) {
      throw new \Exception("Método não definido para a rota {$path}");
    }

    $fullPath = $this->joinPaths($group->prefix, $path);
    $fullName = $group->namePrefix . $name;

    $this->add($method, $fullPath, $module, $controller, $action, $fullName);
  }

  public function group(string $prefix, string $module, string $controller, callable $callback, string $namePrefix = ''): void
  {
    $parent = $this->currentGroup();

    $this->groupStack[] = (object) [
      'prefix' => $this->joinPaths($parent->prefix, $prefix),
      'module' => $module ?: $parent->module,
      'controller' => $controller ?: $parent->controller,
      'namePrefix' => $parent->namePrefix . $namePrefix
    ];

    $callback($this);

    array_pop($this->groupStack);
  }

  public function route(string $name, array $params = [], bool $absolute = true): string
  {
    if (!isset($this->namedRoutes[$name])) {
      throw new \Exception("Rota nomeada não encontrada: {$name}");
    }

    $path = $this->namedRoutes[$name]->path;

    foreach ($params as $key => $value) {
      $path = str_replace('{' . $key . '}', rawurlencode((string) $value), $path);
    }

    if (preg_match('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', $path)) {
      throw new \Exception("Parâmetros obrigatórios ausentes para a rota: {$name}");
    }

    if (!$absolute) {
      return $path;
    }

    return $this->getBaseUrl() . $path;
  }

  private function getBaseUrl(): string
  {
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return rtrim((strpos($host, 'localhost') !== false ? CONF_URL_LOCALHOST : CONF_URL_PRODUCAO), '/');
  }

  private function getRelativeUri(): string
  {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    $basePath = parse_url($this->getBaseUrl(), PHP_URL_PATH) ?? '';

    if ($basePath !== '' && $basePath !== '/' && substr($requestUri, 0, strlen($basePath)) === $basePath) {
      $requestUri = substr($requestUri, strlen($basePath));
    }

    $relativeUri = '/' . ltrim($requestUri, '/');
    $relativeUri = rtrim($relativeUri, '/');

    return $relativeUri === '' ? '/' : $relativeUri;
  }

  public function dispatch(): void
  {
    $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $relativeUri = $this->getRelativeUri();

    foreach ($this->routes as $route) {
      if ($route->method !== $requestMethod) {
        continue;
      }

      if (!preg_match($route->regex, $relativeUri, $matches)) {
        continue;
      }

      $params = $this->buildParams($route, $matches, $relativeUri);
      $this->executeRoute($route, $params);
      return;
    }

    $this->renderNotFound($requestMethod, "A rota {$relativeUri} não foi encontrada");
  }

  private function executeRoute(object $route, array $params): void
  {
    $controllerClass = $this->baseNamespace . $route->module . '\\' . $route->controller;

    if (!class_exists($controllerClass)) {
      $this->renderNotFound($route->method, "Controlador não encontrado: {$controllerClass}", 'Rota não encontrada');
      return;
    }

    $controllerInstance = new $controllerClass();

    if (!method_exists($controllerInstance, $route->action)) {
      $this->renderNotFound($route->method, "Método {$route->action} não encontrado no controlador {$controllerClass}", 'Método não encontrado');
      return;
    }

    call_user_func_array([$controllerInstance, $route->action], $params);
  }

  private function buildParams(object $route, array $matches, string $relativeUri): array
  {
    if (empty($route->params)) {
      return [];
    }

    $uriParts = explode('/', trim($relativeUri, '/'));

    if (empty($uriParts[0])) {
      $uriParts = [];
    }

    $params = [
      'uri' => $relativeUri,
      'params' => $uriParts
    ];

    $positionalParams = [];

    foreach ($route->params as $paramName) {
      $value = isset($matches[$paramName]) ? rawurldecode($matches[$paramName]) : null;

      $params[$paramName] = $value;
      $positionalParams[] = $value;
    }

    if (isset($matches['__tabLink']) && $matches['__tabLink'] !== '') {
      $params['tabLink'] = rawurldecode($matches['__tabLink']);
    }

    return [$params, ...$positionalParams];
  }

  private function pathToRegex(string $path, bool $allowTrailing = false): string
  {
    if ($path === '/') {
      return '#^/$#';
    }

    if ($allowTrailing && preg_match('/\/\{tabLink\}$/', $path)) {
      $basePath = preg_replace('/\/\{tabLink\}$/', '', $path);
      $pattern = preg_quote($basePath, '#');
      $pattern = preg_replace_callback('/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/', function ($matches) {
        return '(?P<' . $matches[1] . '>[^/]+)';
      }, $pattern);
      return '#^' . $pattern . '(?:/(?P<__tabLink>.*))?$#';
    }

    $pattern = preg_quote($path, '#');
    $pattern = preg_replace_callback('/\\\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\\\}/', function ($matches) {
      return '(?P<' . $matches[1] . '>[^/]+)';
    }, $pattern);

    return '#^' . $pattern . '$#';
  }

  private function extractParamNames(string $path): array
  {
    preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $path, $matches);
    return $matches[1] ?? [];
  }

  private function inferAction(string $path): string
  {
    $path = trim($path, '/');

    if ($path === '') {
      return 'index';
    }

    $parts = explode('/', $path);
    $parts = array_reverse($parts);

    foreach ($parts as $part) {
      if ($part === '') {
        continue;
      }

      if (preg_match('/^\{[a-zA-Z_][a-zA-Z0-9_]*\}$/', $part)) {
        continue;
      }

      return $part;
    }

    return 'index';
  }

  private function normalizePath(string $path): string
  {
    $path = trim($path);

    if ($path === '' || $path === '/') {
      return '/';
    }

    return '/' . trim($path, '/');
  }

  private function joinPaths(string $prefix, string $path): string
  {
    $prefix = trim($prefix, '/');
    $path = trim($path, '/');

    if ($prefix === '' && $path === '') {
      return '/';
    }

    if ($prefix === '') {
      return '/' . $path;
    }

    if ($path === '') {
      return '/' . $prefix;
    }

    return '/' . $prefix . '/' . $path;
  }

  private function currentGroup(): object
  {
    return $this->groupStack[count($this->groupStack) - 1];
  }

  private function renderNotFound(string $method, string $message, string $text = ""): void
  {
    if ($text == '') {
      $text = $message;
    }

    if ($method == 'POST') {
      $response = new SimpleResponse("Página não encontrada", $message);
      $response->erro()->json();
      exit;
    }

    $simpleController = new SimpleController();
    $simpleController->setFramework();
    $simpleController->setAssetsCss('global.css');
    $simpleController->setAssetsJs('global.js');
    $simpleController->setAssetsCss('phsa.css');
    $simpleController->setAssetsCss('sistema.css');

    $usuarioLogado = (new SimpleSession())->get('USUARIO') ?? null;

    if (!is_null($usuarioLogado)) {
      $simpleController->setAssetsJs('sistema.js');
      $simpleController->render('template/sistema/404', [
        'message' => $message,
        'text' => $text
      ]);
    } else {
      $simpleController->render('template/site/404', [
        'message' => $message,
        'text' => $text
      ]);
    }

    exit;
  }
}
