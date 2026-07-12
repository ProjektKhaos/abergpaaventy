<?php
// ServiceResult.php – standardiserat resultat från admin-services Ⓐ Style

class ServiceResult
{
    public bool $ok;
    public string $message;
    public array $errors;
    public array $data;
    public ?int $id;

    private function __construct(bool $ok, string $message = '', array $errors = [], array $data = [], ?int $id = null)
    {
        $this->ok = $ok;
        $this->message = $message;
        $this->errors = $errors;
        $this->data = $data;
        $this->id = $id;
    }

    public static function success(string $message, array $data = [], ?int $id = null): self
    {
        return new self(true, $message, [], $data, $id);
    }

    public static function failure(array $errors, array $data = [], ?int $id = null): self
    {
        return new self(false, '', $errors, $data, $id);
    }
}
