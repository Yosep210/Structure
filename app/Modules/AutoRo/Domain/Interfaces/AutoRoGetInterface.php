<?php

namespace App\Modules\AutoRo\Domain\Interfaces;

interface AutoRoGetInterface
{
    public function getRoHistory(string $username): array;

    public function getRoEntry(string $username): array;
}
