<?php

namespace Altcomcr\Client\Tests\Unit;

use Altcomcr\Client\Enums\Destino;
use Altcomcr\Client\Enums\IvaCondicion;
use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Enums\Moneda;
use Altcomcr\Client\Enums\TipoIdentificacion;
use Altcomcr\Client\Enums\TipoNota;
use Altcomcr\Client\Enums\TipoPago;
use Altcomcr\Client\Enums\UnidadMedida;
use Altcomcr\Client\Enums\UnidadServicio;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_moneda_values(): void
    {
        $this->assertSame(1, Moneda::Colones->value);
        $this->assertSame(2, Moneda::Dolares->value);
        $this->assertSame(3, Moneda::Euros->value);
    }

    public function test_moneda_from_iso(): void
    {
        $this->assertSame(Moneda::Colones, Moneda::fromIso('CRC'));
        $this->assertSame(Moneda::Dolares, Moneda::fromIso('USD'));
        $this->assertSame(Moneda::Euros, Moneda::fromIso('EUR'));
    }

    public function test_moneda_from_iso_case_insensitive(): void
    {
        $this->assertSame(Moneda::Colones, Moneda::fromIso('crc'));
        $this->assertSame(Moneda::Dolares, Moneda::fromIso('usd'));
        $this->assertSame(Moneda::Euros, Moneda::fromIso('eur'));
    }

    public function test_moneda_from_iso_invalid_throws(): void
    {
        $this->expectException(\ValueError::class);
        Moneda::fromIso('GBP');
    }

    public function test_moneda_iso_method(): void
    {
        $this->assertSame('CRC', Moneda::Colones->iso());
        $this->assertSame('USD', Moneda::Dolares->iso());
        $this->assertSame('EUR', Moneda::Euros->iso());
    }

    public function test_tipo_pago_values(): void
    {
        $this->assertSame(1, TipoPago::Contado->value);
        $this->assertSame(2, TipoPago::Credito->value);
        $this->assertSame(3, TipoPago::Consignacion->value);
        $this->assertSame(4, TipoPago::Apartado->value);
        $this->assertSame(8, TipoPago::CreditoEstado->value);
        $this->assertSame(10, TipoPago::Credito90Dias->value);
        $this->assertSame(12, TipoPago::NoNacional->value);
    }

    public function test_medio_pago_tipo_values(): void
    {
        $this->assertSame(1, MedioPagoTipo::Efectivo->value);
        $this->assertSame(2, MedioPagoTipo::Tarjeta->value);
        $this->assertSame(3, MedioPagoTipo::Cheque->value);
        $this->assertSame(4, MedioPagoTipo::Transferencia->value);
        $this->assertSame(5, MedioPagoTipo::Terceros->value);
        $this->assertSame(6, MedioPagoTipo::SinpeMovil->value);
    }

    public function test_tipo_identificacion_values(): void
    {
        $this->assertSame('01', TipoIdentificacion::Fisico->value);
        $this->assertSame('02', TipoIdentificacion::Juridico->value);
        $this->assertSame('03', TipoIdentificacion::Dimex->value);
        $this->assertSame('04', TipoIdentificacion::Nite->value);
        $this->assertSame('09', TipoIdentificacion::Extranjero->value);
    }

    public function test_destino_values(): void
    {
        $this->assertSame(0, Destino::Tiquete->value);
        $this->assertSame(1, Destino::Factura->value);
        $this->assertSame(2, Destino::Exportacion->value);
    }

    public function test_iva_condicion_values(): void
    {
        $this->assertSame('01', IvaCondicion::GeneraCreditoIva->value);
        $this->assertSame('02', IvaCondicion::CreditoParcial->value);
        $this->assertSame('03', IvaCondicion::BienesCapital->value);
        $this->assertSame('04', IvaCondicion::GastoCorriente->value);
        $this->assertSame('05', IvaCondicion::Proporcionalidad->value);
    }

    // ---- ResolvableEnum trait ----

    public function test_resolve_from_enum_instance(): void
    {
        $this->assertSame(1, TipoPago::resolve(TipoPago::Contado));
        $this->assertSame('credito', TipoNota::resolve(TipoNota::Credito));
        $this->assertSame('Sp', UnidadServicio::resolve(UnidadServicio::ServiciosProfesionales));
    }

    public function test_resolve_from_raw_value(): void
    {
        $this->assertSame(1, TipoPago::resolve(1));
        $this->assertSame('01', TipoIdentificacion::resolve('01'));
        $this->assertSame('Unid', UnidadMedida::resolve('Unid'));
    }

    public function test_resolve_from_case_name(): void
    {
        $this->assertSame(1, TipoPago::resolve('Contado'));
        $this->assertSame(1, TipoPago::resolve('contado'));
        $this->assertSame('Sp', UnidadServicio::resolve('ServiciosProfesionales'));
        $this->assertSame('01', TipoIdentificacion::resolve('Fisico'));
    }

    public function test_moneda_resolve_from_iso(): void
    {
        $this->assertSame(1, Moneda::resolve('CRC'));
        $this->assertSame(2, Moneda::resolve('usd'));
        $this->assertSame(3, Moneda::resolve('EUR'));
    }

    public function test_moneda_resolve_from_enum_and_value(): void
    {
        $this->assertSame(1, Moneda::resolve(Moneda::Colones));
        $this->assertSame(2, Moneda::resolve(2));
    }

    public function test_enum_json_serialize(): void
    {
        $this->assertSame('1', json_encode(TipoPago::Contado));
        $this->assertSame('"Sp"', json_encode(UnidadServicio::ServiciosProfesionales));
        $this->assertSame('"credito"', json_encode(TipoNota::Credito));
    }
}
