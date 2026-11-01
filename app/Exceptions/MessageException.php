<?php

namespace App\Exceptions;

use App\Concerns\HasToastRedirect;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class MessageException extends HttpResponseException
{
    use HasToastRedirect;

    public function __construct(string $message, string $type = 'error', ?string $redirectTo = null)
    {
        parent::__construct(static::toast($message, $type, $redirectTo));
    }

    public static function required(string $field, ?string $redirectTo = null): static
    {
        return new static("{$field} tidak boleh kosong.", 'error', $redirectTo);
    }

    public static function notFound(string $entity = 'Data', ?string $redirectTo = null): static
    {
        return new static("{$entity} tidak ditemukan.", 'error', $redirectTo);
    }

    public static function tooShort(string $field, int $min, ?string $redirectTo = null): static
    {
        return new static("{$field} minimal harus {$min} karakter.", 'error', $redirectTo);
    }

    public static function tooLong(string $field, int $max, ?string $redirectTo = null): static
    {
        return new static("{$field} tidak boleh lebih dari {$max} karakter.", 'error', $redirectTo);
    }

    public static function invalid(string $field, ?string $redirectTo = null): static
    {
        return new static("Format {$field} tidak valid.", 'error', $redirectTo);
    }

    public static function alreadyExists(string $field, string $value, ?string $redirectTo = null): static
    {
        return new static("{$field} [{$value}] sudah terdaftar di sistem.", 'error', $redirectTo);
    }
}
