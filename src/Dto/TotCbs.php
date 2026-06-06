<?php

namespace DanfseNacional\Dto;

readonly class TotCbs
{
    public function __construct(
        public string $vDifCBS = '',
        public string $vCBS = '',
    ) {}
}
