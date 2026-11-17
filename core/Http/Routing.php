<?php
namespace Core\Http;

class Routing
{
    private array $rotas = [];
    private string $prefixo = '';
    public function get(string $rota, callable|array $retorno)
    {
        $this->corresponder('GET', $rota, $retorno);
    }
    public function post(string $rota, callable|array $retorno)
    {
        $this->corresponder('POST', $rota, $retorno);
    }
    public function put(string $rota, callable|array $retorno)
    {
        $this->corresponder('PUT', $rota, $retorno);
    }
    public function delete(string $rota, callable|array $retorno)
    {
        $this->corresponder('DELETE', $rota, $retorno);
    }
    public function grupo(string $prefixo, callable $retorno)
    {
        $prefixoAntigo = $this->prefixo;
        $this->prefixo = '/' . trim($prefixo, '/');
        $retorno($this);
        $this->prefixo = $prefixoAntigo;
    }

    private function corresponder(string $metodoHttp, string $rota, callable|array $retorno)
    {
        $rotaCompleta = $this->prefixo . '/' . trim($rota, '/');
        if (is_callable($retorno)) {
            $controlador = null;
            $funcao = $retorno;
        }
        if (is_array($retorno)) {
            [$controlador, $funcao] = $retorno;
            if (!class_exists($controlador)) {
                throw new \Exception("Classe da rota {$rotaCompleta} não existe.");
            }
            if (!method_exists($controlador, $funcao)) {
                throw new \Exception("Método {$funcao} da classe {$controlador} não existe.", 1);
            }
        }
        if (isset($this->rotas[$rotaCompleta][$metodoHttp])) {
            throw new \Exception("Rota {$rotaCompleta} duplicada no arquivo de rotas.");
        }
        $this->rotas[$rotaCompleta][$metodoHttp] = [
            'controlador' => $controlador,
            'funcao' => $funcao
        ];
    }
    public function executar()
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $metodoHttp = $_SERVER['REQUEST_METHOD'];
        if (!isset($this->rotas[$url])) {
            throw new \Exception("Rota {$url} não encontrada.", 404);
        }
        if (!isset($this->rotas[$url][$metodoHttp])) {
            throw new \Exception("Rota {$url} existe mais o método {$metodoHttp} não e aceito.", 405);
        }

        extract($this->rotas[$url][$metodoHttp]);
        if (!isset($controlador)) {
            return $funcao();
        }

        $instancia = new $controlador();
        return $instancia->$funcao();
    }
}