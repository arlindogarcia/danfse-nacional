<?php

namespace DanfseNacional\Enums;

enum TpExigSusp: int
{
    case DECISAO_JUDICIAL = 1;
    case PROCESSO_ADMINISTRATIVO = 2;

    public function label(): string
    {
        return match ($this) {
            self::DECISAO_JUDICIAL => 'Exigibilidade Suspensa por Decisão Judicial',
            self::PROCESSO_ADMINISTRATIVO => 'Exigibilidade Suspensa por Processo Administrativo',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
