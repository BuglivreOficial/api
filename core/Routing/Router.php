<?php
namespace Core\Routing;

use Core\Http\Request;
use ReflectionMethod;

/**
 * Descrição da classe Router
 */
class Router
{
    private array $routes = [];
    private string $prefix = '';

    public function get(string $path, array $callback): void
    {
        $this->match('GET', $path, $callback);
    }
    public function post(string $path, array $callback): void
    {
        $this->match('POST', $path, $callback);
    }
    public function put(string $path, array $callback): void
    {
        $this->match('PUT', $path, $callback);
    }
    public function delete(string $path, array $callback): void
    {
        $this->match('DELETE', $path, $callback);
    }

    public function group(string $prefix, callable $callback)
    {
        $oldPrefix = $this->prefix;
        $this->prefix = $prefix;
        $callback($this);
        $this->prefix = $oldPrefix;
    }

    public function match(string $methodHttp, string $path, array $callback)
    {
        //Se a rota ta no group colocar o prefix da rota
        $fullPath = '/' . trim($this->prefix . '/' . trim($path, '/'), '/');
        [$controller, $action] = $callback;
        //Verifica se a classe existe
        if (!class_exists($controller)) {
            throw new \Exception("Classe ```{$controller}```não existe na rota ```{$fullPath}```");
        }
        //verifica se o método da classe existe
        if (!method_exists($controller, $action)) {
            throw new \Exception("Método ```{$action}``` não existe na classe ```{$controller}``` da ```{$fullPath}```");
        }
        //Se a rota e estática e salva nas rotas estáticas
        if (!str_contains($fullPath, '{')) {
            //Verifica se existe rota estática duplicada
            if (isset($this->routes['static'][$fullPath][$methodHttp])) {
                throw new \Exception("Rota <span>{$fullPath}</span> duplicada ");
            }
            $request = $this->reflectionRequest($controller, $action);
            $this->routes['static'][$fullPath][$methodHttp] = [
                'controller' => $controller,
                'action' => $action,
                'request' => $request
            ];
            return;
        }

        //Converte rota dinâmica em regex
        $rota = $this->convertToRegex($fullPath);

        //Verifica se existe rota dinâmica duplicada
        if (isset($this->routes['dynamic'][$rota][$methodHttp])) {
            throw new \Exception("Rota <span>{$fullPath}</span> duplicada ");
        }
        $request = $this->reflectionRequest($controller, $action);

        $this->routes['dynamic'][$rota][$methodHttp] = [
            'controller' => $controller,
            'action' => $action,
            'request' => $request
        ];
    }

    public function convertToRegex($rota)
    {
        // Procura por tudo que estiver dentro de { }
        return '#^' . preg_replace_callback('/\{([^}]+)\}/', function ($matches) {
            // $matches[1] contém apenas o texto de dentro das chaves (ex: "name:str" ou "id")
            $conteudoBloco = $matches[1];

            // Separa o nome do parâmetro e o tipo (se houver)
            $partes = explode(':', $conteudoBloco);
            $nome = $partes[0]; // Agora pega o nome correto de cada um
            $tipo = $partes[1] ?? 'default';

            // Define o padrão regex para cada tipo
            $padroes = [
                'int' => '\d+',
                'str' => '[a-zA-Z]+',
                'bool' => '0|1|true|false',
                'default' => '[a-zA-Z0-9\-_~.%]+'
            ];

            $regexEscolhida = $padroes[$tipo] ?? $padroes['default'];

            // Retorna o grupo nomeado perfeitamente formatado: (?P<nome>padrao)
            return "(?P<" . $nome . ">" . $regexEscolhida . ")";
        }, $rota) . '$#i';
    }

    public function start()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/error';
        $httMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        //Rotas do tipo estáticas tem prioridade na verificação
        if (isset($this->routes['static'][$uri])) {
            if (!isset($this->routes['static'][$uri][$httMethod])) {
                throw new MethodNotAllowedException("Rota existe mais metodo não aceito", 1);
            }
            extract($this->routes['static'][$uri][$httMethod]);
            $instance = new $controller();
            if ($request) {
                $request = new Request();
                $instance->{$action}($request);
                exit;
            }
            $instance->{$action}();
            exit;
        }
        if (isset($this->routes['dynamic'])) {
            foreach ($this->routes['dynamic'] as $key => $value) {
                // Executa a verificação
                if (preg_match($key, $uri, $matches)) {
                    // Filtra o array para manter apenas os parâmetros com nome (ignora os índices numéricos)
                    $parametros = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                    extract($this->routes['dynamic'][$key][$httMethod]);

                    $instance = new $controller();
                    if ($request) {
                        $request = new Request();
                        $instance->{$action}($request, ...$parametros);
                        exit;
                    }
                    $instance->{$action}(...$parametros);
                    exit;
                }
            }
        }

        throw new NotFoundException("Rota <span>{$uri}</span> não encontrada");
    }
    public function reflectionRequest($controller, $action): bool
    {
        // Analizar o método da classe
        $reflection = new ReflectionMethod($controller, $action);

        // Ele pega todos os parâmetros que o método espera
        $parameters = $reflection->getParameters();

        foreach ($parameters as $parameter) {
            // Ele verifica se o parâmetro possui um "Request" (tipo definido)
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType && $type->getName() == "Core\Http\Request") {
                return true;
            }
        }

        return false;
    }
}
