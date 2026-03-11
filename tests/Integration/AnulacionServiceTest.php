<?php

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('anular tiquete', function () {
    $clave = SharedState::$claveTiquete;

    if (empty($clave)) {
        $this->markTestSkipped('No hay tiquete emitido previamente.');
    }

    $response = altcom()
        ->anulacion()
        ->anular($clave);

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Aplicado');
})->group('integration');
