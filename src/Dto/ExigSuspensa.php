<?php

namespace DanfseNacional\Dto;

readonly class ExigSuspensa
{
    public function __construct(
        public string $tpSusp = '',
        public string $nProcesso = '',
    ) {}
}
