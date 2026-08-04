<?php

namespace DanfseNacional\Dto;

readonly class InfoCompl
{
    public function __construct(
        public string $xInfComp = '',
        public string $docRef = '',
        public string $idDocTec = '',
        public string $xPed = '',
        public ?GItemPed $gItemPed = null,
    ) {}
}
