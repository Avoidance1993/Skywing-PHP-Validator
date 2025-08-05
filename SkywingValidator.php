<?php
class SkywingValidator {
    protected array $data;
    protected array $schema;
    protected array $errors = [];

    public function __construct(array $data, array $schema) {
        $this->data = $data;
        $this->schema = $schema;
        $this->validate();
    }

    private function validate() {
        foreach ($this->schema as $field => $config) {
            $rules = $config;
            $messages = [];

            if (is_array($config)) {
                $rules = $config['rules'] ?? '';
                $messages = $config['messages'] ?? [];
            }

            $value = $this->data[$field] ?? null;
            $rules = explode('|', $rules);

            foreach ($rules as $rule) {
                $parts = explode(':', $rule, 2);
                $method = $parts[0];
                $param = $parts[1] ?? null;

                if ($method === 'match') {
                    if (preg_match('/^\[(.+)\]$/', $param, $m)) {
                        $target = $m[1];
                        if (isset($this->data[$target])) {
                            $this->match($field, $value, $target, $this->data[$target], $messages['match'] ?? null);
                        } else {
                            $this->add_error($field, $messages['match'] ?? "$field must match $target");
                        }
                    }
                    continue;
                }

                if ($method === 'in') {
                    $param = explode(',', trim($param, '[]'));
                } elseif ($method === 'between') {
                    $param = array_map('intval', explode(',', trim($param, '[]')));
                } elseif (in_array($method, ['min', 'max'])) {
                    $param = (int) $param;
                }

                if (method_exists($this, $method)) {
                    $this->$method($field, $value, $param, $messages[$method] ?? null);
                }
            }
        }
    }

    private function add_error($field, $message) {
        $this->errors[$field][] = $message;
    }

    private function required($field, $value, $param = null, $message = null) {
        if (empty($value)) {
            $this->add_error($field, $message ?? "$field is required");
        }
    }

    private function email($field, $value, $param = null, $message = null) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->add_error($field, $message ?? "$field must be a valid email");
        }
    }

    private function min($field, $value, $min, $message = null) {
        $length = strlen(trim($value));
        if ($length < $min) {
            $this->add_error($field, $message ?? "$field must be at least $min characters long");
        }
    }

    private function max($field, $value, $max, $message = null) {
        $length = strlen(trim($value));
        if ($length > $max) {
            $this->add_error($field, $message ?? "$field must be at most $max characters long");
        }
    }

    private function in($field, $value, array $options, $message = null) {
        if (!in_array($value, $options)) {
            $this->add_error($field, $message ?? "$field must be one of: " . implode(', ', $options));
        }
    }

    private function between($field, $value, $between, $message = null) {
        $length = strlen(trim($value));
        $min = $between[0];
        $max = $between[1];
        if ($length < $min || $length > $max) {
            $this->add_error($field, $message ?? "$field must be between $min and $max characters long");
        }
    }

    private function match($field, $value, $other_field, $other_value, $message = null) {
        if ($value !== $other_value) {
            $this->add_error($field, $message ?? "$field must match $other_field");
        }
    }

    private function url($field, $value, $param = null, $message = null) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->add_error($field, $message ?? "$field must be a valid URL");
        }
    }

    private function str($field, $value, $param = null, $message = null) {
        if (!is_string($value)) {
            $this->add_error($field, $message ?? "$field must be string");
        }
    }

    private function arr($field, $value, $param = null, $message = null) {
        if (!is_array($value)) {
            $this->add_error($field, $message ?? "$field must be an array");
        }
    }

    private function bool($field, $value, $param = null, $message = null) {
        if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $this->add_error($field, $message ?? "$field must be a boolean");
        }
    }

    private function int($field, $value, $param = null, $message = null) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->add_error($field, $message ?? "$field must be an integer");
        }
    }

    private function num($field, $value, $param = null, $message = null) {
        if (!is_numeric($value)) {
            $this->add_error($field, $message ?? "$field must be a number");
        }
    }

    private function alphanum($field, $value, $param = null, $message = null) {
        if (!ctype_alnum($value)) {
            $this->add_error($field, $message ?? "$field must be alphanumeric");
        }
    }

    private function equals($field, $value, $equals, $message = null) {
        if ($value != $equals) {
            $this->add_error($field, $message ?? "$field value must equal $equals");
        }
    }

    private function custom($field, $value, callable $callback, $message = '') {
        if (!$callback($value)) {
            $this->add_error($field, $message);
        }
    }

    public function errors(): array {
        return $this->errors;
    }

    public function pass(): bool {
        return empty($this->errors);
    }
}
