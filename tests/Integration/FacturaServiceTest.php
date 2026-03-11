<?php

use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\DTOs\MedioPago;
use Altcomcr\Client\DTOs\OtroTexto;
use Altcomcr\Client\Enums\Destino;
use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Enums\Moneda;
use Altcomcr\Client\Enums\TipoIdentificacion;
use Altcomcr\Client\Enums\TipoPago;
use Altcomcr\Client\Enums\UnidadMedida;
use Altcomcr\Client\Enums\UnidadServicio;

beforeAll(function () {
    validateCredentials();
});

beforeEach(function () {
    skipIfInvalidCredentials();
});

test('emitir tiquete sin receptor', function () {
    $documento = uniqueDocumento('TIQ');

    $detalle = [
        new LineaDetalle(
            codigo: 'SERV-01',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio profesional de prueba',
            cantidad: 1,
            precio: 10000,
            cabys: '8313100000100',
            impuesto: 13,
        ),
    ];

    $opciones = [
        'mediopago' => MedioPagoTipo::Efectivo,
        'destino'   => Destino::Tiquete,
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: $opciones,
        );

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Aplicado')
        ->and($response->getClave())->not->toBeEmpty()
        ->and($response->getConsecutivo())->not->toBeEmpty()
        ->and($response->getTotal())->not->toBeNull()
        ->and($response->getXml())->not->toBeNull()
        ->and($response->getTotal())->toBe(11300.0)
        ->and($response->getImpuesto())->toBe(1300.0);

    SharedState::$claveTiquete = $response->getClave();
})->group('integration');

test('emitir factura contado con receptor', function () {
    $documento = uniqueDocumento('FAC');

    $detalle = [
        new LineaDetalle(
            codigo: 'PROD-01',
            unidad: UnidadMedida::Unidad,
            descripcion: 'Producto de prueba unitaria',
            cantidad: 2,
            precio: 5000,
            cabys: '3699002990000',
            impuesto: 13,
        ),
    ];

    $opciones = [
        'cli_cedula' => env('ALTCOM_TEST_CEDULA'),
        'cli_tipo'   => TipoIdentificacion::Juridico,
        'cli_nombre' => 'Empresa de Prueba S.A.',
        'cli_codact' => env('ALTCOM_TEST_ACTIVIDAD', '620101'),
        'mediopago'  => MedioPagoTipo::Efectivo,
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: $opciones,
        );

    expect($response->success)->toBeTrue()
        ->and($response->getClave())->not->toBeEmpty()
        ->and($response->getTotal())->toBe(11300.0);

    SharedState::$claveFacturaContado     = $response->getClave();
    SharedState::$documentoFacturaContado = $documento;
})->group('integration');

test('emitir factura credito', function () {
    $documento = uniqueDocumento('FACCRED');

    $detalle = [
        new LineaDetalle(
            codigo: 'SERV-02',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio a crédito de prueba',
            cantidad: 1,
            precio: 20000,
            cabys: '8313100000100',
            impuesto: 13,
        ),
    ];

    $opciones = [
        'cli_cedula' => env('ALTCOM_TEST_CEDULA'),
        'cli_tipo'   => TipoIdentificacion::Juridico,
        'cli_nombre' => 'Empresa Crédito S.A.',
        'cli_codact' => env('ALTCOM_TEST_ACTIVIDAD', '620101'),
        'mediopago'  => MedioPagoTipo::Transferencia,
        'plazo'      => 30,
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Credito,
            detalle: $detalle,
            opciones: $opciones,
        );

    expect($response->success)->toBeTrue();

    SharedState::$claveFacturaCredito     = $response->getClave();
    SharedState::$documentoFacturaCredito = $documento;
})->group('integration');

test('emitir factura con descuento', function () {
    $documento = uniqueDocumento('FACDESC');

    $detalle = [
        new LineaDetalle(
            codigo: 'SERV-03',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio con descuento prueba',
            cantidad: 1,
            precio: 10000,
            cabys: '8313100000100',
            impuesto: 13,
            descuento: 10,
        ),
    ];

    $opciones = [
        'mediopago' => MedioPagoTipo::Efectivo,
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: $opciones,
        );

    expect($response->success)->toBeTrue()
        // 10000 - 10% = 9000 + 13% IVA = 10170
        ->and($response->getTotal())->toBe(10170.0);
})->group('integration');

test('emitir factura exenta', function () {
    $documento = uniqueDocumento('FACEXE');

    $detalle = [
        new LineaDetalle(
            codigo: 'PROD-EXE',
            unidad: UnidadMedida::Unidad,
            descripcion: 'Producto exento de prueba',
            cantidad: 1,
            precio: 5000,
            cabys: '3699002990000',
            impuesto: 0,
        ),
    ];

    $opciones = [
        'mediopago' => MedioPagoTipo::Efectivo,
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: $opciones,
        );

    expect($response->success)->toBeTrue()
        ->and($response->getTotal())->toBe(5000.0)
        ->and($response->getImpuesto())->toBe(0.0);
})->group('integration');

test('emitir factura multiples lineas', function () {
    $documento = uniqueDocumento('FACMULTI');

    $detalle = [
        new LineaDetalle(
            codigo: 'L1',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio linea 1',
            cantidad: 1,
            precio: 10000,
            cabys: '8313100000100',
            impuesto: 13,
        ),
        new LineaDetalle(
            codigo: 'L2',
            unidad: UnidadMedida::Unidad,
            descripcion: 'Producto linea 2',
            cantidad: 3,
            precio: 1000,
            cabys: '3699002990000',
            impuesto: 13,
        ),
    ];

    $opciones = [
        'mediopago' => MedioPagoTipo::Efectivo,
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: $opciones,
        );

    expect($response->success)->toBeTrue()
        // (10000 + 3000) * 1.13 = 14690
        ->and($response->getTotal())->toBe(14690.0);
})->group('integration');

test('emitir factura medios pago multiples', function () {
    $documento = uniqueDocumento('FACMP');

    $detalle = [
        new LineaDetalle(
            codigo: 'MP-01',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio pago múltiple',
            cantidad: 1,
            precio: 10000,
            cabys: '8313100000100',
            impuesto: 13,
        ),
    ];

    $mediosPago = [
        new MedioPago(tipomp: MedioPagoTipo::Tarjeta, montomp: 5000),
        new MedioPago(tipomp: MedioPagoTipo::Efectivo),
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: [
                'mediopago' => $mediosPago,
            ],
        );

    expect($response->success)->toBeTrue();
})->group('integration');

test('emitir factura con otros nodo', function () {
    $documento = uniqueDocumento('FACOTROS');

    $detalle = [
        new LineaDetalle(
            codigo: 'OT-01',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio con nodo otros',
            cantidad: 1,
            precio: 5000,
            cabys: '8313100000100',
            impuesto: 13,
        ),
    ];

    $otros = [
        new OtroTexto(
            valor: 'TestCode',
            contenido: 'Valor de prueba para nodo otros',
        ),
    ];

    $response = altcom()
        ->facturas()
        ->emitir(
            documento: $documento,
            moneda: Moneda::fromIso('CRC'),
            tipopago: TipoPago::Contado,
            detalle: $detalle,
            opciones: ['mediopago' => MedioPagoTipo::Efectivo],
            otros: $otros,
        );

    expect($response->success)->toBeTrue();
})->group('integration');
