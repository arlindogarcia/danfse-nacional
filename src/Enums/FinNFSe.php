<?php

namespace DanfseNacional\Enums;

enum FinNFSe: int
{
    case REGULAR = 0;

    public function label(): string
    {
        return match ($this) {
            self::REGULAR => 'NFS-e regular',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
