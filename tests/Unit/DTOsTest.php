<?php

use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\DTOs\CargoExtra;
use Altcomcr\Client\DTOs\Exoneracion;
use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\DTOs\MedioPago;
use Altcomcr\Client\DTOs\OtroTexto;

// ---- LineaDetalle ----

test('linea detalle to array minimal', function () {
    $linea = new LineaDetalle(
        codigo: '001',
        unidad: 'Sp',
        descripcion: 'Servicio',
        cantidad: 1,
        precio: 10000,
        cabys: '8313100000100',
    );

    $arr = $linea->toArray();

    expect($arr['codigo'])->toBe('001')
        ->and($arr['unidad'])->toBe('Sp')
        ->and($arr['descripcion'])->toBe('Servicio')
        ->and($arr['cantidad'])->toBe(1.0)
        ->and($arr['precio'])->toBe(10000.0)
        ->and($arr['cabys'])->toBe('8313100000100')
        ->and($arr['impuesto'])->toBe(13)
        ->and($arr['descuento'])->toBe(0.0)
        ->and($arr)->not->toHaveKey('exonerado')
        ->and($arr)->not->toHaveKey('vin')
        ->and($arr)->not->toHaveKey('farma_reg');
});

test('linea detalle with exoneracion', function () {
    $exo = new Exoneracion(
        tipodocumento: '04',
        numerodocumento: 'AL-001',
        nombreinstitucion: '01',
        fechaemision: '2024-01-01T08:00:00',
        porcentajecompra: 13,
    );

    $linea = new LineaDetalle(
        codigo: '001',
        unidad: 'Sp',
        descripcion: 'Exonerado',
        cantidad: 1,
        precio: 5000,
        cabys: '8313100000100',
        impuesto: 13,
        exonerado: $exo,
    );

    $arr = $linea->toArray();

    expect($arr)->toHaveKey('exonerado')
        ->and($arr['exonerado']['tipodocumento'])->toBe('04')
        ->and($arr['exonerado']['porcentajecompra'])->toBe(13);
});

test('linea detalle with vehicle vin', function () {
    $linea = new LineaDetalle(
        codigo: 'V01',
        unidad: 'Unid',
        descripcion: 'Vehículo',
        cantidad: 1,
        precio: 15000000,
        cabys: '4911399000000',
        vin: 'WVWZZZ3CZWE123456',
    );

    $arr = $linea->toArray();
    expect($arr['vin'])->toBe('WVWZZZ3CZWE123456');
});

// ---- Exoneracion ----

test('exoneracion to array omits nulls', function () {
    $exo = new Exoneracion(
        tipodocumento: '04',
        numerodocumento: 'AL-001',
        nombreinstitucion: '01',
        fechaemision: '2024-01-01T08:00:00',
    );

    $arr = $exo->toArray();

    expect($arr)->toHaveCount(4)
        ->and($arr)->not->toHaveKey('articulo')
        ->and($arr)->not->toHaveKey('inciso')
        ->and($arr)->not->toHaveKey('porcentajecompra');
});

test('exoneracion with articulo inciso', function () {
    $exo = new Exoneracion(
        tipodocumento: '02',
        numerodocumento: 'Ley9635',
        nombreinstitucion: '01',
        fechaemision: '2024-01-01T08:00:00',
        articulo: '5',
        inciso: 'a',
        porcentajecompra: 100,
    );

    $arr = $exo->toArray();
    expect($arr['articulo'])->toBe('5')
        ->and($arr['inciso'])->toBe('a')
        ->and($arr['porcentajecompra'])->toBe(100);
});

// ---- MedioPago ----

test('medio pago to array', function () {
    $mp  = new MedioPago(tipomp: 2, montomp: 15000.50);
    $arr = $mp->toArray();

    expect($arr['tipomp'])->toBe('2')
        ->and($arr['montomp'])->toBe('15000.5');
});

test('medio pago without monto', function () {
    $mp  = new MedioPago(tipomp: 1);
    $arr = $mp->toArray();

    expect($arr['tipomp'])->toBe('1')
        ->and($arr)->not->toHaveKey('montomp');
});

// ---- CargoExtra ----

test('cargo extra minimal', function () {
    $cargo = new CargoExtra(
        tipocargo: '06',
        detalle: 'Servicio saloneros 10%',
        monto: 5000,
    );

    $arr = $cargo->toArray();

    expect($arr['tipocargo'])->toBe('06')
        ->and($arr['detalle'])->toBe('Servicio saloneros 10%')
        ->and($arr['monto'])->toBe(5000.0)
        ->and($arr)->not->toHaveKey('tipoid');
});

test('cargo extra tercero', function () {
    $cargo = new CargoExtra(
        tipocargo: '04',
        detalle: 'Cobro tercero',
        monto: 1000,
        tipoid: '01',
        identificacion: '123456789',
        nombre: 'Juan Pérez',
    );

    $arr = $cargo->toArray();
    expect($arr['tipoid'])->toBe('01')
        ->and($arr['identificación'])->toBe('123456789')
        ->and($arr['nombre'])->toBe('Juan Pérez');
});

// ---- OtroTexto ----

test('otro texto minimal', function () {
    $otro = new OtroTexto(valor: 'codigo123', contenido: 'Texto libre');
    $arr  = $otro->toArray();

    expect($arr['valor'])->toBe('codigo123')
        ->and($arr['contenido'])->toBe('Texto libre')
        ->and($arr)->not->toHaveKey('atributo');
});

test('otro texto referencia ice', function () {
    $otro = new OtroTexto(
        valor: 'Orden',
        contenido: 'OC-123456',
        atributo: 'Referencia',
    );

    $arr = $otro->toArray();
    expect($arr['atributo'])->toBe('Referencia')
        ->and($arr['valor'])->toBe('Orden')
        ->and($arr['contenido'])->toBe('OC-123456');
});

// ---- AltcomResponse ----

test('response success', function () {
    $response = AltcomResponse::fromArray([
        'respuesta'   => 'Aplicado',
        'Clave'       => str_repeat('1', 50),
        'Consecutivo' => str_repeat('2', 20),
        'Firmado'     => '2024-01-01T08:00:00',
        'Doc'         => 1,
        'Xml'         => base64_encode('<xml/>'),
        'error'       => 0,
        'total'       => 11300,
        'impuesto'    => 1300,
    ]);

    expect($response->success)->toBeTrue()
        ->and($response->respuesta)->toBe('Aplicado')
        ->and($response->getClave())->toBe(str_repeat('1', 50))
        ->and($response->getConsecutivo())->toBe(str_repeat('2', 20))
        ->and($response->getDoc())->toBe(1)
        ->and($response->getTotal())->toBe(11300.0)
        ->and($response->getImpuesto())->toBe(1300.0)
        ->and($response->getFirmado())->toBe('2024-01-01T08:00:00')
        ->and($response->getXmlDecoded())->toBe('<xml/>');
});

test('response error', function () {
    $response = AltcomResponse::fromArray([
        'respuesta' => 'Documento duplicado',
        'error'     => 1,
    ]);

    expect($response->success)->toBeFalse()
        ->and($response->respuesta)->toBe('Documento duplicado')
        ->and($response->getClave())->toBeNull();
});

test('response hacienda fields', function () {
    $response = AltcomResponse::fromArray([
        'respuesta'       => 'Ok',
        'mensaje'         => 'Aceptado',
        'error'           => 0,
        'HaciendaDetalle' => '',
    ]);

    expect($response->getMensajeHacienda())->toBe('Aceptado')
        ->and($response->getHaciendaDetalle())->toBe('');
});

test('response to array', function () {
    $data     = ['respuesta' => 'Ok', 'error' => 0];
    $response = AltcomResponse::fromArray($data);

    expect($response->toArray())->toBe($data);
});
