<?php

namespace Altcomcr\Client\Tests\Integration;

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
use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class FacturaServiceTest extends TestCase
{
    /** Clave de factura emitida, compartida con otros tests */
    public static string $claveFacturaContado     = '';

    public static string $documentoFacturaContado = '';

    /** Clave de factura a crédito para recibos de pago */
    public static string $claveFacturaCredito     = '';

    public static string $documentoFacturaCredito = '';

    /** Clave de tiquete para anulación */
    public static string $claveTiquete = '';

    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    public function test_emitir_tiquete_sin_receptor(): void
    {
        $documento = static::uniqueDocumento('TIQ');

        $detalle = [
            new LineaDetalle(codigo: 'SERV-01', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio profesional de prueba', cantidad: 1, precio: 10000, cabys: '8531100000100', impuesto: 13,),
        ];

        $opciones = [
            'mediopago' => MedioPagoTipo::Efectivo,
            'destino'   => Destino::Tiquete,
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: $opciones);

        $this->assertTrue($response->success);
        $this->assertSame('Aplicado', $response->respuesta);
        $this->assertNotEmpty($response->getClave());
        $this->assertNotEmpty($response->getConsecutivo());
        $this->assertNotNull($response->getTotal());
        $this->assertNotNull($response->getXml());
        $this->assertSame(11300.0, $response->getTotal());
        $this->assertSame(1300.0, $response->getImpuesto());

        static::$claveTiquete = $response->getClave();
    }

    public function test_emitir_factura_contado_con_receptor(): void
    {
        $documento = static::uniqueDocumento('FAC');

        $detalle = [
            new LineaDetalle(codigo: 'PROD-01', unidad: UnidadMedida::Unidad, descripcion: 'Producto de prueba unitaria', cantidad: 2, precio: 5000, cabys: '2350201000000', impuesto: 13,),
        ];

        $opciones = [
            'cli_cedula' => static::env('ALTCOM_TEST_CEDULA'),
            'cli_tipo'   => TipoIdentificacion::Juridico,
            'cli_nombre' => 'Empresa de Prueba S.A.',
            'cli_codact' => static::env('ALTCOM_TEST_ACTIVIDAD', '620101'),
            'mediopago'  => MedioPagoTipo::Efectivo,
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: $opciones);

        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->getClave());
        $this->assertSame(11300.0, $response->getTotal());

        static::$claveFacturaContado     = $response->getClave();
        static::$documentoFacturaContado = $documento;
    }

    public function test_emitir_factura_credito(): void
    {
        $documento = static::uniqueDocumento('FACCRED');

        $detalle = [
            new LineaDetalle(codigo: 'SERV-02', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio a crédito de prueba', cantidad: 1, precio: 20000, cabys: '8531100000100', impuesto: 13,),
        ];

        $opciones = [
            'cli_cedula' => static::env('ALTCOM_TEST_CEDULA'),
            'cli_tipo'   => TipoIdentificacion::Juridico,
            'cli_nombre' => 'Empresa Crédito S.A.',
            'cli_codact' => static::env('ALTCOM_TEST_ACTIVIDAD', '620101'),
            'mediopago'  => MedioPagoTipo::Transferencia,
            'plazo'      => 30,
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Credito, detalle: $detalle, opciones: $opciones);

        $this->assertTrue($response->success);

        static::$claveFacturaCredito     = $response->getClave();
        static::$documentoFacturaCredito = $documento;
    }

    public function test_emitir_factura_con_descuento(): void
    {
        $documento = static::uniqueDocumento('FACDESC');

        $detalle = [
            new LineaDetalle(codigo: 'SERV-03', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio con descuento prueba', cantidad: 1, precio: 10000, cabys: '8531100000100', impuesto: 13, descuento: 10,),
        ];

        $opciones = [
            'mediopago' => MedioPagoTipo::Efectivo,
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: $opciones);

        $this->assertTrue($response->success);
        // 10000 - 10% = 9000 + 13% IVA = 10170
        $this->assertSame(10170.0, $response->getTotal());
    }

    public function test_emitir_factura_exenta(): void
    {
        $documento = static::uniqueDocumento('FACEXE');

        $detalle = [
            new LineaDetalle(codigo: 'PROD-EXE', unidad: UnidadMedida::Unidad, descripcion: 'Producto exento de prueba', cantidad: 1, precio: 5000, cabys: '2350201000000', impuesto: 0,),
        ];

        $opciones = [
            'mediopago' => MedioPagoTipo::Efectivo,
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: $opciones);

        $this->assertTrue($response->success);
        $this->assertSame(5000.0, $response->getTotal());
        $this->assertSame(0.0, $response->getImpuesto());
    }

    public function test_emitir_factura_multiples_lineas(): void
    {
        $documento = static::uniqueDocumento('FACMULTI');

        $detalle = [
            new LineaDetalle(codigo: 'L1', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio linea 1', cantidad: 1, precio: 10000, cabys: '8531100000100', impuesto: 13,),
            new LineaDetalle(codigo: 'L2', unidad: UnidadMedida::Unidad, descripcion: 'Producto linea 2', cantidad: 3, precio: 1000, cabys: '2350201000000', impuesto: 13,),
        ];

        $opciones = [
            'mediopago' => MedioPagoTipo::Efectivo,
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: $opciones);

        $this->assertTrue($response->success);
        // (10000 + 3000) * 1.13 = 14690
        $this->assertSame(14690.0, $response->getTotal());
    }

    public function test_emitir_factura_medios_pago_multiples(): void
    {
        $documento = static::uniqueDocumento('FACMP');

        $detalle = [
            new LineaDetalle(codigo: 'MP-01', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio pago múltiple', cantidad: 1, precio: 10000, cabys: '8531100000100', impuesto: 13,),
        ];

        $mediosPago = [
            new MedioPago(tipomp: MedioPagoTipo::Tarjeta, montomp: 5000),
            new MedioPago(tipomp: MedioPagoTipo::Efectivo),
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: [
                                  'mediopago' => $mediosPago,
                              ],);

        $this->assertTrue($response->success);
    }

    public function test_emitir_factura_con_otros_nodo(): void
    {
        $documento = static::uniqueDocumento('FACOTROS');

        $detalle = [
            new LineaDetalle(codigo: 'OT-01', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio con nodo otros', cantidad: 1, precio: 5000, cabys: '8531100000100', impuesto: 13,),
        ];

        $otros = [
            new OtroTexto(valor: 'TestCode', contenido: 'Valor de prueba para nodo otros',),
        ];

        $response = static::altcom()
            ->facturas()
            ->emitir(documento: $documento, moneda: Moneda::fromIso('CRC'), tipopago: TipoPago::Contado, detalle: $detalle, opciones: ['mediopago' => MedioPagoTipo::Efectivo], otros: $otros);

        $this->assertTrue($response->success);
    }
}
