<?php

namespace DanfseNacional\Enums;

enum TpBM: int
{
    case ISENCAO = 1;
    case REDUCAO_BC_PERCENTUAL = 2;
    case REDUCAO_BC_VALOR = 3;
    case ALIQUOTA_DIFERENCIADA = 4;

    public function label(): string
    {
        return match ($this) {
            self::ISENCAO => 'Isenção',
            self::REDUCAO_BC_PERCENTUAL => 'Redução da Base de Cálculo (%)',
            self::REDUCAO_BC_VALOR => 'Redução da Base de Cálculo (R$)',
            self::ALIQUOTA_DIFERENCIADA => 'Alíquota Diferenciada',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
