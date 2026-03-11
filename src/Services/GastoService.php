<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

class GastoService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Notificar un gasto o compra recibida.
     *
     * @param string                 $xml           XML completo del emisor codificado en Base64
     * @param int                    $aceptado      1=Aceptado, 3=Rechazado
     * @param string|null            $actividad
     * @param float|null             $iva_acreditable
     * @param float|null             $gasto_acreditable
     * @param BackedEnum|string|null $iva_condicion IvaCondicion enum o '01'-'05'
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function notificar(
        string                 $xml,
        int                    $aceptado = 1,
        ?string                $actividad = null,
        ?float                 $iva_acreditable = null,
        ?float                 $gasto_acreditable = null,
        BackedEnum|string|null $iva_condicion = null,
    ): AltcomResponse {
        $payload             = $this->client->buildBasePayload();
        $payload['xml']      = $xml;
        $payload['aceptado'] = $aceptado;

        if ($actividad !== null) {
            $payload['actividad'] = $actividad;
        }

        if ($iva_acreditable !== null) {
            $payload['iva_acreditable'] = $iva_acreditable;
        }

        if ($gasto_acreditable !== null) {
            $payload['gasto_acreditable'] = $gasto_acreditable;
        }

        if ($iva_condicion !== null) {
            $payload['iva_condicion'] = EnumHelper::value($iva_condicion);
        }

        return $this->client->post('/gasto', $payload);
    }
}
