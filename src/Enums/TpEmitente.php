<?php

namespace DanfseNacional\Enums;

enum TpEmitente: int
{
    case PRESTADOR = 1;
    case TOMADOR = 2;
    case INTERMEDIARIO = 3;

    public function label(): string
    {
        return match ($this) {
            self::PRESTADOR => 'Prestador',
            self::TOMADOR => 'Tomador',
            self::INTERMEDIARIO => 'Intermediário',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
