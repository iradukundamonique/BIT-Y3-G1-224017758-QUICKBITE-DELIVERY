<?php
declare(strict_types=1);

/** Small server-side validator. Only the first error per field is kept. */
final class Validator
{
    public array $errors = [];

    public function __construct(private array $data)
    {
    }

    private function val(string $field): string
    {
        $v = $this->data[$field] ?? '';
        return is_string($v) ? trim($v) : '';
    }

    private function fail(string $field, string $msg): static
    {
        $this->errors[$field] ??= $msg;
        return $this;
    }

    public function required(string $field, string $label): static
    {
        return $this->val($field) === '' ? $this->fail($field, "$label is required.") : $this;
    }

    public function min(string $field, string $label, int $n): static
    {
        $v = $this->val($field);
        return ($v !== '' && mb_strlen($v) < $n) ? $this->fail($field, "$label must be at least $n characters.") : $this;
    }

    public function max(string $field, string $label, int $n): static
    {
        return mb_strlen($this->val($field)) > $n ? $this->fail($field, "$label must be at most $n characters.") : $this;
    }

    public function email(string $field): static
    {
        $v = $this->val($field);
        return ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) ? $this->fail($field, 'Enter a valid email address.') : $this;
    }

    public function pattern(string $field, string $regex, string $msg): static
    {
        $v = $this->val($field);
        return ($v !== '' && !preg_match($regex, $v)) ? $this->fail($field, $msg) : $this;
    }

    public function inList(string $field, string $label, array $allowed): static
    {
        return in_array($this->val($field), $allowed, true) ? $this : $this->fail($field, "Choose a valid $label.");
    }

    /** Positive amount, at most 2 decimals, below 100,000,000. */
    public function money(string $field, string $label): static
    {
        $v = $this->val($field);
        if ($v === '') {
            return $this;
        }
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $v) || (float)$v <= 0) {
            return $this->fail($field, "$label must be a positive amount with up to 2 decimals.");
        }
        return $this;
    }

    public function password(string $field): static
    {
        $v = $this->data[$field] ?? '';
        if (!is_string($v) || $v === '') {
            return $this;
        }
        if (strlen($v) < 8 || !preg_match('/[A-Za-z]/', $v) || !preg_match('/\d/', $v)) {
            return $this->fail($field, 'Password must be at least 8 characters and contain a letter and a number.');
        }
        return $this;
    }

    public function matches(string $field, string $other, string $label): static
    {
        return ($this->data[$field] ?? '') !== ($this->data[$other] ?? '') ? $this->fail($field, "$label does not match.") : $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }
}
