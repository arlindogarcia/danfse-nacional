<?php

namespace DanfseNacional\Enums;

enum SituacaoNFSe: int
{
    case GERADA = 100;
    case SUBSTITUICAO = 101;
    case DECISAO_JUDICIAL = 102;
    case AVULSA = 103;
    case MEI = 107;

    public function label(): string
    {
        return match ($this) {
            self::GERADA => 'NFS-e Gerada',
            self::SUBSTITUICAO => 'NFS-e de Substituição Gerada',
            self::DECISAO_JUDICIAL => 'NFS-e de Decisão Judicial',
            self::AVULSA => 'NFS-e Avulsa',
            self::MEI => 'NFS-e MEI',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
