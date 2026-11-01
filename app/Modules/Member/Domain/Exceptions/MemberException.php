<?php

namespace App\Modules\Member\Domain\Exceptions;

use App\Exceptions\MessageException;
use App\Modules\Member\Domain\Enums\StatusMember;

class MemberException extends MessageException
{
    public static function getUsername(string $username, ?string $redirectTo = null): static
    {
        return new static("Member ({$username}) tidak ditemukan.", 'error', $redirectTo);
    }

    public static function getEmail(string $email, string $username, ?string $redirectTo = null): static
    {
        return new static("Email ({$email}) tidak cocok dengan username ({$username}) tersebut.", 'error', $redirectTo);
    }

    public static function getNetworkNotFound(string $username, ?string $redirectTo = null): static
    {
        return new static("Jaringan member ({$username}) tidak ditemukan.", 'error', $redirectTo);
    }

    public static function getMemberNotFound(string $username, ?string $redirectTo = null): static
    {
        return new static("Member ({$username}) bukan jaringan anda.", 'error', $redirectTo);
    }

    public static function statusUpdate(string $username, StatusMember $newStatus, ?string $redirectTo = null): static
    {
        return new static("Member ({$username}) berhasil di {$newStatus->label()}kan.", 'success', $redirectTo);
    }

    public static function impersonateMe(?string $redirectTo = null): static
    {
        return new static('Tidak dapat melakukan incognito ke akun sendiri.', 'error', $redirectTo);
    }

    public static function memberInactive(string $username, ?string $redirectTo = null): static
    {
        return new static("Member ({$username}) sedang Nonaktif.", 'error', $redirectTo);
    }

    public static function noAccess(?string $redirectTo = null): static
    {
        return new static('Anda tidak memiliki hak akses untuk melakukan aksi ini.', 'error', $redirectTo);
    }
}
