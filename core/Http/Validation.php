<?php 

namespace Core\Http;

class Validation { 
    private array $errors = [];

    /**
     * Valida os dados de acordo com as regras fornecidas.
     * Retorna true se passar, ou false se houver erros.
     */
    public function validate(array $rules, array $data): bool { 
        $this->errors = []; // Limpa erros de validações anteriores

        foreach ($rules as $name => $config) { 
            $fieldValue = $data[$name] ?? null; 
            
            // Suporta formato de string simples ou array configurável
            $rulesString = is_array($config) ? ($config['rules'] ?? '') : $config;
            $label = is_array($config) ? ($config['label'] ?? $name) : $name;

            if (empty($rulesString)) {
                continue;
            }

            foreach (explode('|', $rulesString) as $rule) { 
                $parts = explode(':', $rule, 2); 
                $ruleName = $parts[0];
                $ruleParam = $parts[1] ?? null; 

                // Executa o método de validação correspondente dinamicamente
                if (method_exists($this, $ruleName)) {
                    if (!$this->$ruleName($fieldValue, $ruleParam)) {
                        $this->addError($name, $ruleName, $label, $ruleParam);
                    }
                }
            } 
        } 
        
        return empty($this->errors); 
    } 

    /**
     * Retorna a lista de erros gerados na validação.
     */
    public function getErrors(): array {
        return $this->errors;
    }

    // --- REGRAS DE VALIDAÇÃO ---

    protected function required($value): bool { 
        return $value !== null && $value !== ''; 
    } 

    protected function min($value, $min): bool {
        if ($value === null || $value === '') return true; // Ignora se não for obrigatório
        return mb_strlen((string)$value) >= (int)$min;
    }

    protected function max($value, $max): bool {
        if ($value === null || $value === '') return true;
        return mb_strlen((string)$value) <= (int)$max;
    }

    protected function email($value): bool {
        if ($value === null || $value === '') return true;
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    // --- GERENCIAMENTO DE ERROS ---

    private function addError(string $field, string $rule, string $label, ?string $param): void {
        // Evita duplicar erros para o mesmo campo (ex: se falhar no min, não precisa acumular)
        if (isset($this->errors[$field])) {
            return;
        }

        $messages = [
            'required' => "O campo %s é obrigatório.",
            'min'      => "O campo %s deve ter no mínimo %s caracteres.",
            'max'      => "O campo %s deve ter no máximo %s caracteres.",
            'email'    => "O campo %s deve ser um e-mail válido.",
        ];

        $message = $messages[$rule] ?? "O campo %s é inválido.";
        
        $this->errors[$field] = sprintf($message, $label, $param);
    }
}
