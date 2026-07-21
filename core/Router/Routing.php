<?php

namespace Core\Router;

use Core\Http\Response;
use InvalidArgumentException;
use Throwable;

class Routing
{
    // Estrutura interna: [caminho_normalizado => [METODO => ['callback' => ...]]]
    protected array $rotas = [];

    public function get(string $path, array|callable $callback): void
    {
        $this->registrar('GET', $path, $callback);
    }

    public function post(string $path, array|callable $callback): void
    {
        $this->registrar('POST', $path, $callback);
    }

    public function put(string $path, array|callable $callback): void
    {
        $this->registrar('PUT', $path, $callback);
    }

    public function patch(string $path, array|callable $callback): void
    {
        $this->registrar('PATCH', $path, $callback);
    }

    public function delete(string $path, array|callable $callback): void
    {
        $this->registrar('DELETE', $path, $callback);
    }

    public function registrar(string $metodo, string $caminho, array|callable $callback): void
    {
        $caminhoNormalizado = $this->normalizarCaminho($caminho);
        $this->rotas[$caminhoNormalizado][strtoupper($metodo)] = [
            'callback' => $callback,
        ];
    }

    public function despachar(): void
    {
        $metodoRequisicao = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $caminhoRequisicao = $this->extrairCaminhoRequisicao($_SERVER['REQUEST_URI'] ?? '/');
        $metodosPermitidos = [];

        try {
            foreach ($this->rotas as $caminhoRota => $metodos) {
                // Só continua o fluxo quando a URI atual casar com o padrão da rota.
                $parametros = $this->casarCaminho($caminhoRota, $caminhoRequisicao);
                if ($parametros === null) {
                    continue;
                }

                $metodosPermitidos = array_keys($metodos);
                if (!isset($metodos[$metodoRequisicao])) {
                    // A rota existe, mas não para o verbo HTTP solicitado.
                    Response::enviarJson([
                        'erro' => 'Método não permitido',
                        'metodos_permitidos' => $metodosPermitidos,
                    ], 405, [
                        'Allow' => implode(', ', $metodosPermitidos),
                    ]);
                }

                // O primeiro casamento válido encerra a execução com a resposta do handler.
                $resposta = $this->executarCallback($metodos[$metodoRequisicao]['callback'], $parametros);
                Response::enviarJson($resposta);
            }

            Response::enviarJson([
                'erro' => 'Rota não encontrada',
            ], 404);
        } catch (Throwable $exception) {
            Response::enviarJson([
                'erro' => 'Erro interno do servidor',
                'mensagem' => $exception->getMessage(),
            ], 500);
        }
    }

    protected function executarCallback(array|callable $callback, array $parametros): mixed
    {
        if (is_array($callback)) {
            [$classeOuInstancia, $metodo] = $callback;
            // Permite registrar tanto a classe quanto uma instância já criada.
            $handler = is_string($classeOuInstancia) ? new $classeOuInstancia() : $classeOuInstancia;

            if (!method_exists($handler, $metodo)) {
                throw new InvalidArgumentException('Método do handler da rota não foi encontrado.');
            }

            return $handler->{$metodo}(...array_values($parametros));
        }

        return $callback(...array_values($parametros));
    }

    protected function extrairCaminhoRequisicao(string $uri): string
    {
        $caminho = parse_url($uri, PHP_URL_PATH) ?: '/';

        return $this->normalizarCaminho($caminho);
    }

    protected function normalizarCaminho(string $caminho): string
    {
        $caminhoSemEspacos = trim($caminho);
        if ($caminhoSemEspacos === '' || $caminhoSemEspacos === '/') {
            return '/';
        }

        // Mantém um formato único para evitar diferenças entre "/rota" e "rota/".
        return '/' . trim($caminhoSemEspacos, '/');
    }

    protected function casarCaminho(string $caminhoRota, string $caminhoRequisicao): ?array
    {
        $nomesParametros = [];
        $segmentos = explode('/', trim($caminhoRota, '/'));
        $segmentosCompilados = array_map(
            static function (string $segmento) use (&$nomesParametros): string {
                // Converte placeholders como {id} em grupos regex capturáveis.
                if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segmento, $matches) === 1) {
                    $nomesParametros[] = $matches[1];

                    return '([^/]+)';
                }

                return preg_quote($segmento, '#');
            },
            $segmentos
        );

        $padrao = '#^/' . implode('/', $segmentosCompilados) . '$#';
        if (!preg_match($padrao, $caminhoRequisicao, $matches)) {
            return null;
        }

        array_shift($matches);

        if ($nomesParametros === []) {
            return [];
        }

        // Combina os nomes definidos na rota com os valores capturados na URI.
        return array_combine($nomesParametros, $matches) ?: [];
    }
}
