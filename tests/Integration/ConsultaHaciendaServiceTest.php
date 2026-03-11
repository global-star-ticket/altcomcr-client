<?php

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('consultar hacienda', function () {
    $clave = SharedState::$claveFacturaContado;

    if (empty($clave)) {
        $this->markTestSkipped('No hay factura emitida previamente.');
    }

    // Esperar un momento para que Hacienda procese
    sleep(5);

    $response = altcom()
        ->consultas()
        ->consultarHacienda($clave);

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Ok')
        ->and($response->getClave())->not->toBeEmpty()
        ->and($response->getMensajeHacienda())->toBeIn(['Aceptado', 'Rechazado']);
})->group('integration');

test('consulta interna', function () {
    $documento = SharedState::$documentoFacturaContado;

    if (empty($documento)) {
        $this->markTestSkipped('No hay documento de factura previamente emitido.');
    }

    $response = altcom()
        ->consultas()
        ->consultarInterno(documento: $documento);

    expect($response->success)->toBeTrue()
        ->and($response->getClave())->not->toBeEmpty()
        ->and($response->getXml())->not->toBeNull();
})->group('integration');
