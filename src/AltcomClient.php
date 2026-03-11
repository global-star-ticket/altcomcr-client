<?php

namespace Altcomcr\Client;

use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\Exceptions\AltcomApiException;
use Altcomcr\Client\Exceptions\AltcomConnectionException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class AltcomClient
{
    protected string $baseUrl;

    protected int    $timeout;

    protected int    $retries;

    protected int    $retryDelay;

    protected string $usuario;

    protected string $clave;

    protected string $cedula;

    protected string $sucursal;

    protected string $terminal;

    protected string $actividad;

    protected Client $httpClient;

    /**
     * @param string      $usuario    Email de la cuenta Altcom
     * @param string      $clave      Contraseña de la cuenta (se envía como SHA1 automáticamente)
     * @param string      $cedula     Cédula del emisor
     * @param string      $sucursal   Código de sucursal (default: 001)
     * @param string      $terminal   Código de terminal (default: 00001)
     * @param string      $actividad  Código de actividad económica (opcional)
     * @param bool        $sandbox    Usar ambiente de pruebas (default: true)
     * @param int         $timeout    Timeout en segundos (default: 30)
     * @param int         $retries    Reintentos en caso de fallo de conexión (default: 3)
     * @param int         $retryDelay Delay entre reintentos en milisegundos (default: 100)
     * @param string|null $baseUrl    URL base personalizada (sobreescribe sandbox)
     */
    public function __construct(
        string  $usuario,
        string  $clave,
        string  $cedula,
        string  $sucursal = '001',
        string  $terminal = '00001',
        string  $actividad = '',
        bool    $sandbox = true,
        int     $timeout = 30,
        int     $retries = 3,
        int     $retryDelay = 100,
        ?string $baseUrl = null,
    ) {
        $this->usuario    = $usuario;
        $this->clave      = $clave;
        $this->cedula     = $cedula;
        $this->sucursal   = $sucursal;
        $this->terminal   = $terminal;
        $this->actividad  = $actividad;
        $this->timeout    = $timeout;
        $this->retries    = $retries;
        $this->retryDelay = $retryDelay;

        $this->baseUrl = $baseUrl ?? ($sandbox ? 'https://sandbox.altcomcr.net/api' : 'https://www.altcomcr.net/f/api');

        $this->httpClient = new Client([
                                           'base_uri' => rtrim($this->baseUrl, '/').'/',
                                           'timeout'  => $this->timeout,
                                           'headers'  => [
                                               'Content-Type' => 'application/json',
                                               'Accept'       => 'application/json',
                                           ],
                                       ]);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getCedula(): string
    {
        return $this->cedula;
    }

    public function getSucursal(): string
    {
        return $this->sucursal;
    }

    public function getTerminal(): string
    {
        return $this->terminal;
    }

    public function getActividad(): string
    {
        return $this->actividad;
    }

    public function buildCredentials(): array
    {
        return [
            'usuario' => $this->usuario,
            'clave'   => sha1($this->clave),
        ];
    }

    public function buildBasePayload(): array
    {
        $payload           = $this->buildCredentials();
        $payload['cedula'] = $this->cedula;

        if ($this->sucursal !== '001') {
            $payload['sucursal'] = $this->sucursal;
        }

        if ($this->terminal !== '00001') {
            $payload['terminal'] = $this->terminal;
        }

        return $payload;
    }

    public function post(string $endpoint, array $payload): AltcomResponse
    {
        $endpoint = ltrim($endpoint, '/');
        $attempt  = 0;

        while (true) {
            $attempt++;

            try {
                $response = $this->httpClient->post($endpoint, [
                    'json' => $payload,
                ]);

                break;
            } catch (GuzzleException $e) {
                if ($attempt >= $this->retries) {
                    throw new AltcomConnectionException("Error de conexión con Altcom: {$e->getMessage()}", '', 0, $e);
                }

                usleep($this->retryDelay * 1000);
            }
        }

        $body = $response->getBody()
            ->getContents();
        $data = json_decode($body, true) ?? [];

        $altcomResponse = AltcomResponse::fromArray($data);

        if (! $altcomResponse->success) {
            throw AltcomApiException::fromResponse($data);
        }

        return $altcomResponse;
    }
}
