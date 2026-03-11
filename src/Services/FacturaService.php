<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\DTOs\CargoExtra;
use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\DTOs\MedioPago;
use Altcomcr\Client\DTOs\OtroTexto;
use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

class FacturaService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Emitir una factura, tiquete o factura de exportación.
     *
     * @param string            $documento Número de control interno
     * @param BackedEnum|int    $moneda    Moneda enum, ISO string o 1=colones, 2=dólares, 3=euros
     * @param BackedEnum|int    $tipopago  TipoPago enum o 1=contado, 2=crédito, etc.
     * @param LineaDetalle[]    $detalle
     * @param array             $opciones  Campos opcionales: cli_cedula, cli_tipo, cli_nombre, cli_codact, plazo, mediopago, destino, actividad, observacion
     * @param OtroTexto[]|null  $otros
     * @param CargoExtra[]|null $cargos
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function emitir(
        string         $documento,
        BackedEnum|int $moneda,
        BackedEnum|int $tipopago,
        array          $detalle,
        array          $opciones = [],
        ?array         $otros = null,
        ?array         $cargos = null,
    ): AltcomResponse {
        $payload = $this->client->buildBasePayload();

        $payload['documento'] = $documento;
        $payload['moneda']    = EnumHelper::value($moneda);
        $payload['tipopago']  = EnumHelper::value($tipopago);

        // Campos opcionales del cliente (resuelve enums automáticamente)
        $camposOpcionales = [
            'cli_cedula',
            'cli_tipo',
            'cli_nombre',
            'cli_codact',
            'plazo',
            'destino',
            'actividad',
            'observacion',
        ];

        foreach ($camposOpcionales as $campo) {
            if (isset($opciones[$campo])) {
                $payload[$campo] = EnumHelper::value($opciones[$campo]);
            }
        }

        // Actividad económica por defecto desde config
        if (! isset($payload['actividad']) && $this->client->getActividad()) {
            $payload['actividad'] = $this->client->getActividad();
        }

        // Medio de pago: puede ser enum, entero simple o array de MedioPago
        if (isset($opciones['mediopago'])) {
            $mp = $opciones['mediopago'];
            if (is_array($mp) && isset($mp[0]) && $mp[0] instanceof MedioPago) {
                $payload['mediopago'] = array_map(fn(MedioPago $m) => $m->toArray(), $mp);
            } else {
                $payload['mediopago'] = EnumHelper::value($mp);
            }
        }

        // Líneas de detalle
        $payload['detalle'] = array_map(fn(LineaDetalle $linea) => $linea->toArray(), $detalle);

        // Otros
        if ($otros) {
            $payload['otros'] = array_map(fn(OtroTexto $otro) => $otro->toArray(), $otros);
        }

        // Cargos
        if ($cargos) {
            $payload['cargos'] = array_map(fn(CargoExtra $cargo) => $cargo->toArray(), $cargos);
        }

        return $this->client->post('/recepcion', $payload);
    }
}
