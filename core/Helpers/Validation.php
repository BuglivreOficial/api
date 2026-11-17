<?php
namespace Core\Helpers;

use Core\Http\Request;

class Validation
{
    private Request $request;
    private array $erros = [];
    public function __construct()
    {
        $this->request = new Request();
    }
    public function rules(array $rules)
    {
        foreach ($rules as $key => $value) {
            $value = explode('|', $value);
            foreach ($value as $rule) {
                if (str_contains($rule, ':')) {
                    [$rule, $param] = explode(':', $rule);
                }
                $data = $this->request->body($key);
                if ($rule == 'required' && !$this->required($data)) {
                    $this->erros[$key][] = 'O campo é obrigatorio.';
                    break;
                }
                if ($rule == 'min' && !$this->min($data, $param)) {
                    $this->erros[$key][] = "Mínimo {$param} caracteres.";
                }
            }
        }
        return !empty($this->erros);
    }
    public function getErros()
    {
        return $this->erros;
    }
    public function min(?string $str, int $size)
    {
        if (strlen(trim($str)) >= $size) {
            return true;
        }
        return false;
    }
    public function required(?string $value)
    {
        if (isset($value) && !empty(trim($value))) {
            return true;
        }
        return false;
    }
}