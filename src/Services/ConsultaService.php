<?php

namespace Altcomcr\Client\Services;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\DTOs\AltcomResponse;

class ConsultaService
{
    public function __construct(protected AltcomClient $client)
    {
    }

    /**
     * Consultar la respuesta de Hacienda para un documento electrónico.
     *
     * @param string $clavedoc Clave de 50 dígitos del documento
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function consultarHacienda(string $clavedoc): AltcomResponse
    {
        $payload             = $this->client->buildCredentials();
        $payload['clavedoc'] = $clavedoc;

        return $this->client->post('/consulta', $payload);
    }

    /**
     * Consultar un documento solo en ALTCOM (consulta interna).
     *
     * @param string      $documento Número de control interno
     * @param string|null $cedula
     * @param string|null $sucursal
     * @param string|null $tipo      "venta", "credito" o "debito"
     * @return \Altcomcr\Client\DTOs\AltcomResponse
     * @throws \Altcomcr\Client\Exceptions\AltcomApiException
     * @throws \Altcomcr\Client\Exceptions\AltcomConnectionException
     */
    public function consultarInterno(
        string  $documento,
        ?string $cedula = null,
        ?string $sucursal = null,
        ?string $tipo = null,
    ): AltcomResponse {
        $payload              = $this->client->buildCredentials();
        $payload['documento'] = $documento;

        if ($cedula !== null) {
            $payload['cedula'] = $cedula;
        }

        if ($sucursal !== null) {
            $payload['sucursal'] = $sucursal;
        }

        if ($tipo !== null) {
            $payload['tipo'] = $tipo;
        }

        return $this->client->post('/consulta_int', $payload);
    }

    /**
     * Consultar vencimiento de la cuenta.
     */
    public function saldo(): AltcomResponse
    {
        $payload = $this->client->buildCredentials();

        return $this->client->post('/saldo', $payload);
    }

    /**
     * Consultar vencimiento de la llave criptográfica y actividades económicas.
     */
    public function llave(?string $cedula = null, ?string $sucursal = null): AltcomResponse
    {
        $payload           = $this->client->buildCredentials();
        $payload['cedula'] = $cedula ?? $this->client->getCedula();

        if ($sucursal !== null) {
            $payload['sucursal'] = $sucursal;
        }

        return $this->client->post('/llave', $payload);
    }
}
