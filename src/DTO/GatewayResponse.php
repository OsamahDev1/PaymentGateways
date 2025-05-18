<?php

// src/DTO/GatewayResponse.php
namespace kmalarifi97\PaymentGateways\DTO;

class GatewayResponse
{
    public function __construct(
        public readonly bool   $ok,
        public readonly array  $data = [],
        public readonly ?array $meta = null

    ) {}
}
