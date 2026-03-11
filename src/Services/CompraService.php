<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\DTOs\MedioPago;
use Altcomcr\Client\Enums\EnumHelper;
use BackedEnum;

class CompraService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Emitir factura de compra para proveedores fuera del régimen tradicional.
     *
     * @param string               $documento
     * @param string               $fecha
     * @param string               $tipodoc
     * @param BackedEnum|int       $moneda    Moneda enum o 1=colones, 2=dólares, 3=euros
     * @param BackedEnum|int|array $mediopago MedioPagoTipo enum, 1-6 o array de MedioPago
     * @param BackedEnum|int       $tipopago  TipoPago enum o 1=contado, 2=crédito, etc.
     * @param array                $proveedor prov_cedula, prov_tipo (acepta enum), prov_nombre, etc.
     * @param LineaDetalle[]       $detalle
     * @param int|null             $plazo
     * @param string|null          $actividad
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function emitir(
        string               $documento,
        string               $fecha,
        string               $tipodoc,
        BackedEnum|int       $moneda,
        BackedEnum|int|array $mediopago,
        BackedEnum|int       $tipopago,
        array                $proveedor,
        array                $detalle,
        ?int                 $plazo = null,
        ?string              $actividad = null,
    ): AltcomResponse {
        $payload = $this->client->buildBasePayload();

        $payload['documento'] = $documento;
        $payload['fecha']     = $fecha;
        $payload['tipodoc']   = $tipodoc;
        $payload['moneda']    = EnumHelper::value($moneda);
        $payload['tipopago']  = EnumHelper::value($tipopago);

        // Medio de pago
        if (is_array($mediopago) && isset($mediopago[0]) && $mediopago[0] instanceof MedioPago) {
            $payload['mediopago'] = array_map(fn(MedioPago $m) => $m->toArray(), $mediopago);
        } else {
            $payload['mediopago'] = EnumHelper::value($mediopago);
        }

        // Datos del proveedor (resuelve enums en prov_tipo)
        $camposProveedor = ['prov_cedula', 'prov_tipo', 'prov_nombre', 'prov_telefono', 'prov_cp', 'prov_direccion', 'prov_email'];
        foreach ($camposProveedor as $campo) {
            if (isset($proveedor[$campo])) {
                $payload[$campo] = EnumHelper::value($proveedor[$campo]);
            }
        }

        if ($plazo !== null) {
            $payload['plazo'] = $plazo;
        }

        if ($actividad !== null) {
            $payload['actividad'] = $actividad;
        } elseif ($this->client->getActividad()) {
            $payload['actividad'] = $this->client->getActividad();
        }

        $payload['detalle'] = array_map(fn(LineaDetalle $linea) => $linea->toArray(), $detalle);

        return $this->client->post('/compra', $payload);
    }
}
