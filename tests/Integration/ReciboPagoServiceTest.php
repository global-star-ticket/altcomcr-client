<?php

use Altcomcr\Client\Enums\MedioPagoTipo;

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('emitir recibo pago', function () {
    $clave = SharedState::$claveFacturaCredito;

    if (empty($clave)) {
        $this->markTestSkipped('No hay factura a crédito emitida previamente.');
    }

    $documento = uniqueDocumento('PAG');

    $response = altcom()
        ->recibosPago()
        ->emitir(
            clavedoc: $clave,
            documento: $documento,
            monto: 10000,
            mediopago: MedioPagoTipo::Transferencia,
            detalle: 'Abono parcial - prueba automatizada',
        );

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Aplicado')
        ->and($response->getClave())->not->toBeEmpty();
})->group('integration');
