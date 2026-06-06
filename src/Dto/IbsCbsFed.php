<?php

namespace DanfseNacional\Dto;

readonly class IbsCbsFed
{
    public function __construct(
        public string $pCBS = '',
        public string $pRedAliqCBS = '',
        public string $pAliqEfetCBS = '',
    ) {}
}
