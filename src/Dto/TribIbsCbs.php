<?php

namespace DanfseNacional\Dto;

readonly class TribIbsCbs
{
    public function __construct(
        public ?SitClasIbsCbs $gIBSCBS = null,
    ) {}
}
