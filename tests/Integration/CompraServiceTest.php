<?php

use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Enums\Moneda;
use Altcomcr\Client\Enums\TipoIdentificacion;
use Altcomcr\Client\Enums\TipoPago;
use Altcomcr\Client\Enums\UnidadServicio;

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('emitir factura compra extranjero', function () {
    $documento = uniqueDocumento('FC');
    $fecha     = date('Y-m-d H:i:s');
    $tipodoc   = '16'; // Extranjero

    $proveedor = [
        'prov_cedula'    => '999999999999',
        'prov_tipo'      => TipoIdentificacion::Extranjero,
        'prov_nombre'    => 'Proveedor Extranjero Test LLC',
        'prov_email'     => 'test@proveedor-externo.com',
        'prov_telefono'  => '00000000',
        'prov_direccion' => 'Test City, Test Country',
    ];

    $detalle = [
        new LineaDetalle(
            codigo: 'EXT-01',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio de hosting prueba',
            cantidad: 1,
            precio: 50,
            cabys: '8315100000000',
            impuesto: 13,
        ),
    ];

    $response = altcom()
        ->compras()
        ->emitir(
            documento: $documento,
            fecha: $fecha,
            tipodoc: $tipodoc,
            moneda: Moneda::fromIso('USD'),
            mediopago: MedioPagoTipo::Transferencia,
            tipopago: TipoPago::Contado,
            proveedor: $proveedor,
            detalle: $detalle,
        );

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Aplicado')
        ->and($response->getClave())->not->toBeEmpty();
})->group('integration');
