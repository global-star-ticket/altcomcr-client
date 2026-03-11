<?php

use Altcomcr\Client\Altcom;
use Altcomcr\Client\AltcomClient;
use Altcomcr\Client\AltcomFactory;
use Altcomcr\Client\Services\AnulacionService;
use Altcomcr\Client\Services\CompraService;
use Altcomcr\Client\Services\ConsultaService;
use Altcomcr\Client\Services\FacturaService;
use Altcomcr\Client\Services\GastoService;
use Altcomcr\Client\Services\NotaService;
use Altcomcr\Client\Services\ReciboPagoService;

beforeEach(function () {
    $this->client = new AltcomClient(
        usuario: 'test@example.com',
        clave: 'secret123',
        cedula: '123456789',
        sucursal: '002',
        terminal: '00003',
        actividad: '620101',
        sandbox: true,
    );
});

test('sandbox url', function () {
    expect($this->client->getBaseUrl())->toBe('https://sandbox.altcomcr.net/api');
});

test('production url', function () {
    $client = new AltcomClient(
        usuario: 'test@example.com',
        clave: 'secret',
        cedula: '123',
        sandbox: false,
    );

    expect($client->getBaseUrl())->toBe('https://www.altcomcr.net/f/api');
});

test('custom base url', function () {
    $client = new AltcomClient(
        usuario: 'test@example.com',
        clave: 'secret',
        cedula: '123',
        baseUrl: 'https://custom.api.com/v1',
    );

    expect($client->getBaseUrl())->toBe('https://custom.api.com/v1');
});

test('getters', function () {
    expect($this->client->getCedula())->toBe('123456789')
        ->and($this->client->getSucursal())->toBe('002')
        ->and($this->client->getTerminal())->toBe('00003')
        ->and($this->client->getActividad())->toBe('620101');
});

test('build credentials hashes password', function () {
    $credentials = $this->client->buildCredentials();

    expect($credentials['usuario'])->toBe('test@example.com')
        ->and($credentials['clave'])->toBe(sha1('secret123'))
        ->and($credentials)->not->toHaveKey('cedula');
});

test('build base payload includes cedula', function () {
    $payload = $this->client->buildBasePayload();

    expect($payload['usuario'])->toBe('test@example.com')
        ->and($payload['clave'])->toBe(sha1('secret123'))
        ->and($payload['cedula'])->toBe('123456789')
        ->and($payload['sucursal'])->toBe('002')
        ->and($payload['terminal'])->toBe('00003');
});

test('build base payload omits default sucursal and terminal', function () {
    $client = new AltcomClient(
        usuario: 'test@example.com',
        clave: 'secret',
        cedula: '123',
    );

    $payload = $client->buildBasePayload();

    expect($payload)->not->toHaveKey('sucursal')
        ->and($payload)->not->toHaveKey('terminal');
});

test('altcom make factory', function () {
    $altcom = Altcom::make(usuario: 'test@example.com', clave: 'secret', cedula: '123');

    expect($altcom)->toBeInstanceOf(Altcom::class)
        ->and($altcom->client())->toBeInstanceOf(AltcomClient::class);
});

test('altcom service accessors', function () {
    $altcom = Altcom::make('a', 'b', 'c');

    expect($altcom->facturas())->toBeInstanceOf(FacturaService::class)
        ->and($altcom->notas())->toBeInstanceOf(NotaService::class)
        ->and($altcom->consultas())->toBeInstanceOf(ConsultaService::class)
        ->and($altcom->gastos())->toBeInstanceOf(GastoService::class)
        ->and($altcom->compras())->toBeInstanceOf(CompraService::class)
        ->and($altcom->recibosPago())->toBeInstanceOf(ReciboPagoService::class)
        ->and($altcom->anulacion())->toBeInstanceOf(AnulacionService::class);
});

test('altcom factory class', function () {
    $factory = new AltcomFactory(
        sandbox: true,
        timeout: 15,
        retries: 2,
        retryDelay: 50,
    );

    $altcom = $factory->make(usuario: 'test@example.com', clave: 'secret', cedula: '123');

    expect($altcom)->toBeInstanceOf(Altcom::class)
        ->and($altcom->client()->getBaseUrl())->toBe('https://sandbox.altcomcr.net/api');
});
