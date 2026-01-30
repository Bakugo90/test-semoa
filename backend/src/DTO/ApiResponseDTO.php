<?php

namespace App\DTO;

class ApiResponseDTO
{
    public function __construct(
        public string $message = 'success',
        public mixed $data = null,
        public array $meta = []
    ) {}

    public static function success(mixed $data = null, array $meta = []): self
    {
        return new self('success', $data, $meta);
    }

    public static function error(string $errorMessage, array $meta = []): self
    {
        return new self('error', null, array_merge($meta, ['error' => $errorMessage]));
    }
}
