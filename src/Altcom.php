<?php

namespace Altcomcr\Client;

use Altcomcr\Client\Services\AnulacionService;
use Altcomcr\Client\Services\CompraService;
use Altcomcr\Client\Services\ConsultaService;
use Altcomcr\Client\Services\FacturaService;
use Altcomcr\Client\Services\GastoService;
use Altcomcr\Client\Services\NotaService;
use Altcomcr\Client\Services\ReciboPagoService;

class Altcom
{
    protected AltcomClient $client;

    /**
     * @param string      $usuario    Email de la cuenta Altcom
     * @param string      $clave      Contraseña de la cuenta
     * @param string      $cedula     Cédula del emisor
     * @param string      $sucursal   Código de sucursal (default: 001)
     * @param string      $terminal   Código de terminal (default: 00001)
     * @param string      $actividad  Código de actividad económica (opcional)
     * @param bool        $sandbox    Usar ambiente de pruebas (default: true)
     * @param int         $timeout    Timeout en segundos (default: 30)
     * @param int         $retries    Reintentos en caso de fallo (default: 3)
     * @param int         $retryDelay Delay entre reintentos en ms (default: 100)
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
        $this->client = new AltcomClient(usuario: $usuario, clave: $clave, cedula: $cedula, sucursal: $sucursal, terminal: $terminal, actividad: $actividad, sandbox: $sandbox, timeout: $timeout, retries: $retries, retryDelay: $retryDelay, baseUrl: $baseUrl,);
    }

    /**
     * Factory estático para crear una instancia.
     */
    public static function make(
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
    ): self {
        return new self(usuario: $usuario, clave: $clave, cedula: $cedula, sucursal: $sucursal, terminal: $terminal, actividad: $actividad, sandbox: $sandbox, timeout: $timeout, retries: $retries, retryDelay: $retryDelay, baseUrl: $baseUrl,);
    }

    public function facturas(): FacturaService
    {
        return new FacturaService($this->client);
    }

    public function notas(): NotaService
    {
        return new NotaService($this->client);
    }

    public function consultas(): ConsultaService
    {
        return new ConsultaService($this->client);
    }

    public function gastos(): GastoService
    {
        return new GastoService($this->client);
    }

    public function compras(): CompraService
    {
        return new CompraService($this->client);
    }

    public function recibosPago(): ReciboPagoService
    {
        return new ReciboPagoService($this->client);
    }

    public function anulacion(): AnulacionService
    {
        return new AnulacionService($this->client);
    }

    public function client(): AltcomClient
    {
        return $this->client;
    }
}
