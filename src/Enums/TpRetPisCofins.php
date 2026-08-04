<?php

namespace DanfseNacional\Enums;

enum TpRetPisCofins: int
{
    case RETIDO = 1;
    case NAO_RETIDO = 2;

    public function label(): string
    {
        return match ($this) {
            self::RETIDO => 'PIS/COFINS Retido',
            self::NAO_RETIDO => 'PIS/COFINS Não Retido',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
