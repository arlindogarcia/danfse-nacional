<?php

namespace DanfseNacional\Dto;

readonly class IbsCbsUf
{
    public function __construct(
        public string $pIBSUF = '',
        public string $pRedAliqUF = '',
        public string $pAliqEfetUF = '',
    ) {}
}
