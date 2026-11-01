<?php

namespace App\Concerns;

trait HasEnumOptions
{
    /**
     * @return list<array{value: string, label: string, color: string|null}>
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => (string) ($case->value ?? $case->name),
            'label' => method_exists($case, 'label') ? $case->label() : $case->name,
            'color' => method_exists($case, 'color') ? $case->color() : null,
        ])->values()->all();
    }
}
