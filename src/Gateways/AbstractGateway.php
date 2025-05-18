<?php
namespace kmalarifi97\PaymentGateways\Gateways;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use kmalarifi97\PaymentGateways\DTO\GatewayResponse;
use kmalarifi97\PaymentGateways\Exceptions\GatewayException;

abstract class AbstractGateway
{
    /** Allow children to reuse basic error formatting */
    protected function toGatewayResponse(Response $response): GatewayResponse
    {
        if ($response->successful()) {
            return new GatewayResponse(true, $response->json());
        }

        Log::error(static::class.' call failed', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        throw new GatewayException('Gateway call failed ('.static::class.')');
    }
}
