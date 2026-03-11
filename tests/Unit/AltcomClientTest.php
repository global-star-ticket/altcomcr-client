<?php

namespace Altcomcr\Client\Tests\Unit;

use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\Services\AnulacionService;
use Altcomcr\Client\Services\CompraService;
use Altcomcr\Client\Services\ConsultaService;
use Altcomcr\Client\Services\FacturaService;
use Altcomcr\Client\Services\GastoService;
use Altcomcr\Client\Services\NotaService;
use Altcomcr\Client\Services\ReciboPagoService;
use Altcomcr\Client\Altcom;
use Altcomcr\Client\AltcomFactory;
use PHPUnit\Framework\TestCase;

class AltcomClientTest extends TestCase
{
    private AltcomClient $client;

    protected function setUp(): void
    {
        $this->client = new AltcomClient(usuario: 'test@example.com', clave: 'secret123', cedula: '123456789', sucursal: '002', terminal: '00003', actividad: '620101', sandbox: true,);
    }

    public function test_sandbox_url(): void
    {
        $this->assertSame('https://sandbox.altcomcr.net/api', $this->client->getBaseUrl());
    }

    public function test_production_url(): void
    {
        $client = new AltcomClient(usuario: 'test@example.com', clave: 'secret', cedula: '123', sandbox: false,);

        $this->assertSame('https://www.altcomcr.net/f/api', $client->getBaseUrl());
    }

    public function test_custom_base_url(): void
    {
        $client = new AltcomClient(usuario: 'test@example.com', clave: 'secret', cedula: '123', baseUrl: 'https://custom.api.com/v1',);

        $this->assertSame('https://custom.api.com/v1', $client->getBaseUrl());
    }

    public function test_getters(): void
    {
        $this->assertSame('123456789', $this->client->getCedula());
        $this->assertSame('002', $this->client->getSucursal());
        $this->assertSame('00003', $this->client->getTerminal());
        $this->assertSame('620101', $this->client->getActividad());
    }

    public function test_build_credentials_hashes_password(): void
    {
        $credentials = $this->client->buildCredentials();

        $this->assertSame('test@example.com', $credentials['usuario']);
        $this->assertSame(sha1('secret123'), $credentials['clave']);
        $this->assertArrayNotHasKey('cedula', $credentials);
    }

    public function test_build_base_payload_includes_cedula(): void
    {
        $payload = $this->client->buildBasePayload();

        $this->assertSame('test@example.com', $payload['usuario']);
        $this->assertSame(sha1('secret123'), $payload['clave']);
        $this->assertSame('123456789', $payload['cedula']);
        $this->assertSame('002', $payload['sucursal']);
        $this->assertSame('00003', $payload['terminal']);
    }

    public function test_build_base_payload_omits_default_sucursal_and_terminal(): void
    {
        $client = new AltcomClient(usuario: 'test@example.com', clave: 'secret', cedula: '123',);

        $payload = $client->buildBasePayload();

        $this->assertArrayNotHasKey('sucursal', $payload);
        $this->assertArrayNotHasKey('terminal', $payload);
    }

    public function test_altcom_make_factory(): void
    {
        $altcom = Altcom::make(usuario: 'test@example.com', clave: 'secret', cedula: '123');

        $this->assertInstanceOf(Altcom::class, $altcom);
        $this->assertInstanceOf(AltcomClient::class, $altcom->client());
    }

    public function test_altcom_service_accessors(): void
    {
        $altcom = Altcom::make('a', 'b', 'c');

        $this->assertInstanceOf(FacturaService::class, $altcom->facturas());
        $this->assertInstanceOf(NotaService::class, $altcom->notas());
        $this->assertInstanceOf(ConsultaService::class, $altcom->consultas());
        $this->assertInstanceOf(GastoService::class, $altcom->gastos());
        $this->assertInstanceOf(CompraService::class, $altcom->compras());
        $this->assertInstanceOf(ReciboPagoService::class, $altcom->recibosPago());
        $this->assertInstanceOf(AnulacionService::class, $altcom->anulacion());
    }

    public function test_altcom_factory_class(): void
    {
        $factory = new AltcomFactory(sandbox: true, timeout: 15, retries: 2, retryDelay: 50,);

        $altcom = $factory->make(usuario: 'test@example.com', clave: 'secret', cedula: '123');

        $this->assertInstanceOf(Altcom::class, $altcom);
        $this->assertSame('https://sandbox.altcomcr.net/api', $altcom->client()
            ->getBaseUrl());
    }
}
