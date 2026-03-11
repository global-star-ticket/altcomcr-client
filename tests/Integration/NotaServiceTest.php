<?php

use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\Enums\TipoNota;
use Altcomcr\Client\Enums\UnidadMedida;

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('nota credito por monto', function () {
    $clave = SharedState::$claveFacturaContado;

    if (empty($clave)) {
        $this->markTestSkipped('No hay factura de contado emitida previamente. Ejecutar FacturaServiceTest primero.');
    }

    $documento = uniqueDocumento('NCM');

    $response = altcom()->notas()->emitirPorMonto(
        clavedoc: $clave,
        documento: $documento,
        tipo: TipoNota::Credito,
        detalle: 'Nota de crédito por monto - prueba automatizada',
        monto_prod: 5000,
        impuesto_prod: 13,
    );

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Aplicado')
        ->and($response->getClave())->not->toBeEmpty();
})->group('integration');

test('nota credito por detalle', function () {
    $clave = SharedState::$claveFacturaContado;

    if (empty($clave)) {
        $this->markTestSkipped('No hay factura de contado emitida previamente. Ejecutar FacturaServiceTest primero.');
    }

    $documento = uniqueDocumento('NCD');

    $detalle = [
        new LineaDetalle(
            codigo: 'PROD-01',
            unidad: UnidadMedida::Unidad,
            descripcion: 'Devolución producto de prueba',
            cantidad: 1,
            precio: 5000,
            cabys: '3699002990000',
            impuesto: 13,
        ),
    ];

    $response = altcom()->notas()->emitirPorDetalle(
        clavedoc: $clave,
        documento: $documento,
        tipo: TipoNota::Credito,
        detalle: $detalle,
        observacion: 'Devolución por prueba automatizada',
    );

    expect($response->success)->toBeTrue()
        ->and($response->getClave())->not->toBeEmpty();
})->group('integration');

test('nota debito por monto', function () {
    $clave = SharedState::$claveFacturaContado;

    if (empty($clave)) {
        $this->markTestSkipped('No hay factura de contado emitida previamente. Ejecutar FacturaServiceTest primero.');
    }

    $documento = uniqueDocumento('NDM');

    $response = altcom()->notas()->emitirPorMonto(
        clavedoc: $clave,
        documento: $documento,
        tipo: TipoNota::Debito,
        detalle: 'Nota de débito por monto - prueba automatizada',
        monto_serv: 1000,
        impuesto_serv: 13,
    );

    expect($response->success)->toBeTrue()
        ->and($response->getClave())->not->toBeEmpty();
})->group('integration');
