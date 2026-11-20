<?php
namespace Core\Http;

use Exception;
use PDO;

class Routing
{
    /**
     * Padrões regex para cada tipo de parâmetro.
     * Uso: /usuarios/{id:int}  ou  /arquivos/{path:mixed}
     * Sem tipo explícito, o padrão é "str".
     */
    private const PARAMETER_PATTERNS = [
        'str'   => '[^/]+',                  // um segmento de texto
        'int'   => '-?\d+',                  // inteiro, com sinal opcional
        'float' => '-?\d+(?:\.\d+)?',        // decimal
        'bool'  => 'true|false|1|0',         // booleano
        'mixed' => '.+',                     // qualquer valor, inclusive com "/"
    ];

    private array $staticRoutes = [];   // rotas sem parâmetros, indexadas por caminho
    private array $dynamicRoutes = [];  // rotas com parâmetros, testadas por regex
    private string $prefix = '/';

    public function get(string $route, callable|array $handler): void
    {
        $this->register('GET', $route, $handler);
    }

    public function post(string $route, callable|array $handler): void
    {
        $this->register('POST', $route, $handler);
    }

    public function put(string $route, callable|array $handler): void
    {
        $this->register('PUT', $route, $handler);
    }

    public function patch(string $route, callable|array $handler): void
    {
        $this->register('PATCH', $route, $handler);
    }

    public function delete(string $route, callable|array $handler): void
    {
        $this->register('DELETE', $route, $handler);
    }

    public function group(string $prefix, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $this->prefix = $this->normalizePath($previousPrefix . '/' . $prefix);

        try {
            $callback($this);
        } finally {
            $this->prefix = $previousPrefix;
        }
    }

    public function start(PDO $db): mixed
    {
        $path = $this->normalizePath(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
        $httpMethod = $_SERVER['REQUEST_METHOD'];

        if (isset($this->staticRoutes[$path][$httpMethod])) {
            return $this->invoke($this->staticRoutes[$path][$httpMethod], $db, []);
        }

        $pathExists = isset($this->staticRoutes[$path]);

        foreach ($this->dynamicRoutes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }

            $pathExists = true;

            if ($route['method'] !== $httpMethod) {
                continue;
            }

            $params = $this->extractParameters($route['parameters'], $matches);
            return $this->invoke($route, $db, $params);
        }

        if ($pathExists) {
            throw new Exception("Rota {$path} existe mas o método {$httpMethod} não é aceito.", 405);
        }

        throw new Exception("Rota {$path} não encontrada.", 404);
    }

    private function register(string $httpMethod, string $route, callable|array $handler): void
    {
        $fullPath = $this->normalizePath($this->prefix . '/' . $route);

        [$controller, $method] = is_array($handler) ? $handler : [null, $handler];

        if ($controller !== null) {
            if (!class_exists($controller)) {
                throw new Exception("Classe da rota {$fullPath} não existe.");
            }
            if (!method_exists($controller, $method)) {
                throw new Exception("Método {$method} da classe {$controller} não existe.");
            }
        }

        $compiled = $this->compile($fullPath);

        $definition = [
            'method'     => $httpMethod,
            'path'       => $fullPath,
            'regex'      => $compiled['regex'],
            'parameters' => $compiled['parameters'],
            'controller' => $controller,
            'handler'    => $method,
        ];

        if ($compiled['parameters'] === []) {
            if (isset($this->staticRoutes[$fullPath][$httpMethod])) {
                throw new Exception("Rota {$fullPath} duplicada no arquivo de rotas.");
            }
            $this->staticRoutes[$fullPath][$httpMethod] = $definition;
            return;
        }

        foreach ($this->dynamicRoutes as $existing) {
            if ($existing['method'] === $httpMethod && $existing['regex'] === $compiled['regex']) {
                throw new Exception("Rota {$fullPath} duplicada no arquivo de rotas.");
            }
        }

        $this->dynamicRoutes[] = $definition;
    }

    /**
     * Converte um caminho como /usuarios/{id:int} em uma regex e
     * registra o tipo de cada parâmetro.
     */
    private function compile(string $path): array
    {
        $regex = '';
        $parameters = [];

        $parts = preg_split('#(\{[^{}]+\})#', $path, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($parts as $part) {
            if (!preg_match('#^\{([a-zA-Z_][a-zA-Z0-9_]*)(?::([a-z]+))?\}$#', $part, $matches)) {
                $regex .= preg_quote($part, '#');
                continue;
            }

            $name = $matches[1];
            $type = $matches[2] ?? 'str';

            if (!isset(self::PARAMETER_PATTERNS[$type])) {
                throw new Exception("Tipo de parâmetro '{$type}' não suportado na rota {$path}.");
            }
            if (isset($parameters[$name])) {
                throw new Exception("Parâmetro '{$name}' repetido na rota {$path}.");
            }

            $parameters[$name] = $type;
            $regex .= '(?P<' . $name . '>' . self::PARAMETER_PATTERNS[$type] . ')';
        }

        return [
            'regex'      => '#^' . $regex . '$#D',
            'parameters' => $parameters,
        ];
    }

    private function extractParameters(array $parameters, array $matches): array
    {
        $values = [];

        foreach ($parameters as $name => $type) {
            $values[$name] = $this->castParameter($type, rawurldecode($matches[$name]));
        }

        return $values;
    }

    private function castParameter(string $type, string $value): mixed
    {
        return match ($type) {
            'int'   => (int) $value,
            'float' => (float) $value,
            'bool'  => $value === 'true' || $value === '1',
            default => $value, // str e mixed
        };
    }

    private function invoke(array $route, PDO $db, array $params): mixed
    {
        $arguments = array_values($params);

        if ($route['controller'] === null) {
            return ($route['handler'])(...$arguments);
        }

        $controller = new $route['controller']();
        return $controller->{$route['handler']}($db, ...$arguments);
    }

    private function normalizePath(string $path): string
    {
        return '/' . trim(preg_replace('#/+#', '/', $path), '/');
    }
}