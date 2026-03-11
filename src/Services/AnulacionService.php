<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;

class AnulacionService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Anular una factura de compra, factura o tiquete mediante nota de crédito.
     *
     * @param string $clavedoc Clave de 50 dígitos del documento a anular
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function anular(string $clavedoc): AltcomResponse
    {
        $payload             = $this->client->buildCredentials();
        $payload['clavedoc'] = $clavedoc;

        return $this->client->post('/anular', $payload);
    }
}
