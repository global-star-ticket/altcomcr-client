<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

class NotaService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Emitir nota de crédito o débito por monto.
     *
     * @param string            $clavedoc
     * @param string            $documento
     * @param BackedEnum|string $tipo TipoNota enum o "credito"/"debito"
     * @param string            $detalle
     * @param float             $monto_serv
     * @param int               $impuesto_serv
     * @param float             $monto_prod
     * @param int               $impuesto_prod
     * @param string|null       $observacion
     * @param string|null       $actividad
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function emitirPorMonto(
        string            $clavedoc,
        string            $documento,
        BackedEnum|string $tipo,
        string            $detalle,
        float             $monto_serv = 0,
        int               $impuesto_serv = 0,
        float             $monto_prod = 0,
        int               $impuesto_prod = 0,
        ?string           $observacion = null,
        ?string           $actividad = null,
    ): AltcomResponse {
        $payload = $this->client->buildBasePayload();

        $payload['clavedoc']      = $clavedoc;
        $payload['documento']     = $documento;
        $payload['tipo']          = EnumHelper::value($tipo);
        $payload['detallado']     = '0';
        $payload['detalle']       = $detalle;
        $payload['monto_serv']    = $monto_serv;
        $payload['impuesto_serv'] = $impuesto_serv;
        $payload['monto_prod']    = $monto_prod;
        $payload['impuesto_prod'] = $impuesto_prod;

        if ($observacion !== null) {
            $payload['observación'] = $observacion;
        }

        if ($actividad !== null) {
            $payload['actividad'] = $actividad;
        }

        return $this->client->post('/recepcion_nota', $payload);
    }

    /**
     * Emitir nota de crédito o débito por detalle de productos.
     *
     * @param string            $clavedoc
     * @param string            $documento
     * @param BackedEnum|string $tipo TipoNota enum o "credito"/"debito"
     * @param LineaDetalle[]    $detalle
     * @param string|null       $observacion
     * @param string|null       $actividad
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function emitirPorDetalle(
        string            $clavedoc,
        string            $documento,
        BackedEnum|string $tipo,
        array             $detalle,
        ?string           $observacion = null,
        ?string           $actividad = null,
    ): AltcomResponse {
        $payload = $this->client->buildBasePayload();

        $payload['clavedoc']  = $clavedoc;
        $payload['documento'] = $documento;
        $payload['tipo']      = EnumHelper::value($tipo);
        $payload['detallado'] = '1';

        if ($observacion !== null) {
            $payload['observación'] = $observacion;
        }

        if ($actividad !== null) {
            $payload['actividad'] = $actividad;
        }

        $payload['detalle'] = array_map(fn(LineaDetalle $linea) => $linea->toArray(), $detalle);

        return $this->client->post('/recepcion_nota', $payload);
    }
}
