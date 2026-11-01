<?php

namespace App\Modules\Member\Domain\ValueObjects;

use App\Modules\Member\Domain\Exceptions\MemberException;

readonly class Name
{
    public function __construct(
        public string $value
    ) {
        $this->ensureIsValid($this->value);
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private function ensureIsValid(string $value): void
    {
        $trimmed = trim($value);

        if (empty($trimmed)) {
            throw MemberException::required('Nama');
        }

        if (mb_strlen($trimmed) > 255) {
            throw MemberException::tooLong('Nama', 255);
        }
    }
}
