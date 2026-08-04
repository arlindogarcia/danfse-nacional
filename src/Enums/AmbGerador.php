<?php

namespace DanfseNacional\Enums;

enum AmbGerador: int
{
    case PREFEITURA = 1;
    case SISTEMA_NACIONAL = 2;

    public function label(): string
    {
        return match ($this) {
            self::PREFEITURA => 'Prefeitura',
            self::SISTEMA_NACIONAL => 'Sistema Nacional da NFS-e',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
