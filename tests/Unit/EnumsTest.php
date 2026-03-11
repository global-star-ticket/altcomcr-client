<?php

use Altcomcr\Client\Enums\Destino;
use Altcomcr\Client\Enums\IvaCondicion;
use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Enums\Moneda;
use Altcomcr\Client\Enums\TipoIdentificacion;
use Altcomcr\Client\Enums\TipoNota;
use Altcomcr\Client\Enums\TipoPago;
use Altcomcr\Client\Enums\UnidadMedida;
use Altcomcr\Client\Enums\UnidadServicio;

test('moneda values', function () {
    expect(Moneda::Colones->value)->toBe(1)
        ->and(Moneda::Dolares->value)->toBe(2)
        ->and(Moneda::Euros->value)->toBe(3);
});

test('moneda from iso', function () {
    expect(Moneda::fromIso('CRC'))->toBe(Moneda::Colones)
        ->and(Moneda::fromIso('USD'))->toBe(Moneda::Dolares)
        ->and(Moneda::fromIso('EUR'))->toBe(Moneda::Euros);
});

test('moneda from iso case insensitive', function () {
    expect(Moneda::fromIso('crc'))->toBe(Moneda::Colones)
        ->and(Moneda::fromIso('usd'))->toBe(Moneda::Dolares)
        ->and(Moneda::fromIso('eur'))->toBe(Moneda::Euros);
});

test('moneda from iso invalid throws', function () {
    Moneda::fromIso('GBP');
})->throws(ValueError::class);

test('moneda iso method', function () {
    expect(Moneda::Colones->iso())->toBe('CRC')
        ->and(Moneda::Dolares->iso())->toBe('USD')
        ->and(Moneda::Euros->iso())->toBe('EUR');
});

test('tipo pago values', function () {
    expect(TipoPago::Contado->value)->toBe(1)
        ->and(TipoPago::Credito->value)->toBe(2)
        ->and(TipoPago::Consignacion->value)->toBe(3)
        ->and(TipoPago::Apartado->value)->toBe(4)
        ->and(TipoPago::CreditoEstado->value)->toBe(8)
        ->and(TipoPago::Credito90Dias->value)->toBe(10)
        ->and(TipoPago::NoNacional->value)->toBe(12);
});

test('medio pago tipo values', function () {
    expect(MedioPagoTipo::Efectivo->value)->toBe(1)
        ->and(MedioPagoTipo::Tarjeta->value)->toBe(2)
        ->and(MedioPagoTipo::Cheque->value)->toBe(3)
        ->and(MedioPagoTipo::Transferencia->value)->toBe(4)
        ->and(MedioPagoTipo::Terceros->value)->toBe(5)
        ->and(MedioPagoTipo::SinpeMovil->value)->toBe(6);
});

test('tipo identificacion values', function () {
    expect(TipoIdentificacion::Fisico->value)->toBe('01')
        ->and(TipoIdentificacion::Juridico->value)->toBe('02')
        ->and(TipoIdentificacion::Dimex->value)->toBe('03')
        ->and(TipoIdentificacion::Nite->value)->toBe('04')
        ->and(TipoIdentificacion::Extranjero->value)->toBe('09');
});

test('destino values', function () {
    expect(Destino::Tiquete->value)->toBe(0)
        ->and(Destino::Factura->value)->toBe(1)
        ->and(Destino::Exportacion->value)->toBe(2);
});

test('iva condicion values', function () {
    expect(IvaCondicion::GeneraCreditoIva->value)->toBe('01')
        ->and(IvaCondicion::CreditoParcial->value)->toBe('02')
        ->and(IvaCondicion::BienesCapital->value)->toBe('03')
        ->and(IvaCondicion::GastoCorriente->value)->toBe('04')
        ->and(IvaCondicion::Proporcionalidad->value)->toBe('05');
});

// ---- ResolvableEnum trait ----

test('resolve from enum instance', function () {
    expect(TipoPago::resolve(TipoPago::Contado))->toBe(1)
        ->and(TipoNota::resolve(TipoNota::Credito))->toBe('credito')
        ->and(UnidadServicio::resolve(UnidadServicio::ServiciosProfesionales))->toBe('Sp');
});

test('resolve from raw value', function () {
    expect(TipoPago::resolve(1))->toBe(1)
        ->and(TipoIdentificacion::resolve('01'))->toBe('01')
        ->and(UnidadMedida::resolve('Unid'))->toBe('Unid');
});

test('resolve from case name', function () {
    expect(TipoPago::resolve('Contado'))->toBe(1)
        ->and(TipoPago::resolve('contado'))->toBe(1)
        ->and(UnidadServicio::resolve('ServiciosProfesionales'))->toBe('Sp')
        ->and(TipoIdentificacion::resolve('Fisico'))->toBe('01');
});

test('moneda resolve from iso', function () {
    expect(Moneda::resolve('CRC'))->toBe(1)
        ->and(Moneda::resolve('usd'))->toBe(2)
        ->and(Moneda::resolve('EUR'))->toBe(3);
});

test('moneda resolve from enum and value', function () {
    expect(Moneda::resolve(Moneda::Colones))->toBe(1)
        ->and(Moneda::resolve(2))->toBe(2);
});

test('enum json serialize', function () {
    expect(json_encode(TipoPago::Contado))->toBe('1')
        ->and(json_encode(UnidadServicio::ServiciosProfesionales))->toBe('"Sp"')
        ->and(json_encode(TipoNota::Credito))->toBe('"credito"');
});
