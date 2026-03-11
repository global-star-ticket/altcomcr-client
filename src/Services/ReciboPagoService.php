<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

class ReciboPagoService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Emitir recibo electrónico de pago para abonos a facturas de crédito.
     *
     * @param string         $clavedoc
     * @param string         $documento
     * @param float          $monto
     * @param BackedEnum|int $mediopago MedioPagoTipo enum o 1-6
     * @param string|null    $detalle
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function emitir(
        string         $clavedoc,
        string         $documento,
        float          $monto,
        BackedEnum|int $mediopago,
        ?string        $detalle = null,
    ): AltcomResponse {
        $payload = $this->client->buildBasePayload();

        $payload['clavedoc']  = $clavedoc;
        $payload['documento'] = $documento;
        $payload['monto']     = $monto;
        $payload['mediopago'] = EnumHelper::value($mediopago);

        if ($detalle !== null) {
            $payload['detalle'] = $detalle;
        }

        return $this->client->post('/recibopago', $payload);
    }
}
