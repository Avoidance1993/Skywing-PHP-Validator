<?php
class SW_Validator {
    protected array $data;
    protected array $schema;
    protected array $errors = [];

    public function __construct(array $data, array $schema) {
        $this->data = $data;
        $this->schema = $schema;
        $this->validate();
    }

    private function validate() {
        foreach ($this->schema as $field => $rules) {
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
                            $this->match($field, $value, $target, $this->data[$target]);
                        } 
                        else {
                            $this->errors[$field][] = "$field must match $target";
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
                    $this->$method($field, $value, $param);
                }
            }
        }
    }

    private function required($field, $value) {
        if (empty($value)) {
            $this->errors[$field][] = "$field is required";
        }
        return $this;
    }

    private function email($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = "$field must be a valid email";
        }
        return $this;
    }

    private function min($field, $value, $min) {
        $length = strlen(trim($value));
        if ($length < $min) {
            $this->errors[$field][] = "$field must be at least $min characters long";
        }
        return $this;
    }

    private function max($field, $value, $max) {
        $length = strlen(trim($value));
        if ($length > $max) {
            $this->errors[$field][] = "$field must be at most $max characters long";
        }
        return $this;
    }

    private function in($field, $value, array $options) {
        if (!in_array($value, $options)) {
            $this->errors[$field][] = "$field must be one of: " . implode(', ', $options);
        }
        return $this;
    }

    private function between($field, $value, $between) {
        $length = strlen(trim($value));
        $min = $between[0];
        $max = $between[1];
        if ($length < $min || $length > $max) {
            $this->errors[$field][] = "$field must be between $min and $max characters long";
        }
        return $this;
    }

    private function match($field, $value, $other_field, $other_value) {
        if ($value !== $other_value) {
            $this->errors[$field][] = "$field must match $other_field";
        }
        return $this;
    }

    private function url($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field][] = "$field must be a valid URL";
        }
        return $this;
    }

    private function str($field, $value) {
        if (!is_string($value)) {
            $this->errors[$field][] = "$field must be string";
        }
        return $this;
    }

    private function arr($field, $value) {
        if (!is_array($value)) {
            $this->errors[$field][] = "$field must be an array";
        }
        return $this;
    }

    private function bool($field, $value) {
        if (!in_array($value, [true, false, 0, 1, '0', '1'], true)) {
            $this->errors[$field][] = "$field must be a boolean";
        }
        return $this;
    }

    private function int($field, $value) {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field][] = "$field must be an integer";
        }
        return $this;
    }

    private function num($field, $value) {
        if (!is_numeric($value)) {
            $this->errors[$field][] = "$field must be a number";
        }
        return $this;
    }

    private function alphanum($field, $value) {
        if (!ctype_alnum($value)) {
            $this->errors[$field][] = "$field must be alphanumeric";
        }
        return $this;
    }

    private function custom($field, $value, callable $callback, $message = '') {
        if (!$callback($value)) {
            $this->errors[$field][] = $message;
        }
        return $this;
    }

    public function errors(): array {
        return $this->errors;
    }

    public function pass(): bool {
        return empty($this->errors);
    }
}
