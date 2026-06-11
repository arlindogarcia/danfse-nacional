<?php

namespace DanfseNacional\Dto;

readonly class Servico
{
    public function __construct(
        public ?LocPrest $locPrest = null,
        public ?CServ $cServ = null,
        public ?InfoCompl $infoCompl = null,
        public ?Obra $obra = null,
        public ?AtvEvento $atvEvento = null,
    ) {}
}
