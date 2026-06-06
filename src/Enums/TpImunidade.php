<?php

namespace DanfseNacional\Enums;

enum TpImunidade: int
{
    case NAO_INFORMADO = 0;
    case PATRIMONIO_RENDA_SERVICOS = 1;
    case TEMPLOS = 2;
    case PARTIDOS_ENTIDADES = 3;
    case LIVROS_JORNAIS_PERIODICOS = 4;
    case FONOGRAMAS = 5;

    public function label(): string
    {
        return match ($this) {
            self::NAO_INFORMADO => 'Imunidade (tipo não informado na nota de origem)',
            self::PATRIMONIO_RENDA_SERVICOS => 'Patrimônio, renda ou serviços, uns dos outros (CF88, Art 150, VI, a)',
            self::TEMPLOS => 'Templos de qualquer culto (CF88, Art 150, VI, b)',
            self::PARTIDOS_ENTIDADES => 'Patrimônio, renda ou serviços dos partidos políticos, entidades sindicais e instituições de educação e assistência social, sem fins lucrativos (CF88, Art 150, VI, c)',
            self::LIVROS_JORNAIS_PERIODICOS => 'Livros, jornais, periódicos e o papel destinado a sua impressão (CF88, Art 150, VI, d)',
            self::FONOGRAMAS => 'Fonogramas e videofonogramas musicais produzidos no Brasil (CF88, Art 150, VI, e)',
        };
    }

    public static function labelFor(string $value): string
    {
        $case = self::tryFrom((int) $value);
        return $case ? $case->label() : '-';
    }
}
