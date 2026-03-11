<?php

namespace Altcomcr\Client\Tests\Unit;

use Altcomcr\Client\DTOs\AltcomResponse;
use Altcomcr\Client\DTOs\CargoExtra;
use Altcomcr\Client\DTOs\Exoneracion;
use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\DTOs\MedioPago;
use Altcomcr\Client\DTOs\OtroTexto;
use PHPUnit\Framework\TestCase;

class DTOsTest extends TestCase
{
    // ---- LineaDetalle ----

    public function test_linea_detalle_to_array_minimal(): void
    {
        $linea = new LineaDetalle(codigo: '001', unidad: 'Sp', descripcion: 'Servicio', cantidad: 1, precio: 10000, cabys: '8531100000100',);

        $arr = $linea->toArray();

        $this->assertSame('001', $arr['codigo']);
        $this->assertSame('Sp', $arr['unidad']);
        $this->assertSame('Servicio', $arr['descripcion']);
        $this->assertSame(1.0, $arr['cantidad']);
        $this->assertSame(10000.0, $arr['precio']);
        $this->assertSame('8531100000100', $arr['cabys']);
        $this->assertSame(13, $arr['impuesto']);
        $this->assertSame(0.0, $arr['descuento']);
        $this->assertArrayNotHasKey('exonerado', $arr);
        $this->assertArrayNotHasKey('vin', $arr);
        $this->assertArrayNotHasKey('farma_reg', $arr);
    }

    public function test_linea_detalle_with_exoneracion(): void
    {
        $exo = new Exoneracion(tipodocumento: '04', numerodocumento: 'AL-001', nombreinstitucion: '01', fechaemision: '2024-01-01T08:00:00', porcentajecompra: 13,);

        $linea = new LineaDetalle(codigo: '001', unidad: 'Sp', descripcion: 'Exonerado', cantidad: 1, precio: 5000, cabys: '8531100000100', impuesto: 13, exonerado: $exo,);

        $arr = $linea->toArray();

        $this->assertArrayHasKey('exonerado', $arr);
        $this->assertSame('04', $arr['exonerado']['tipodocumento']);
        $this->assertSame(13, $arr['exonerado']['porcentajecompra']);
    }

    public function test_linea_detalle_with_vehicle_vin(): void
    {
        $linea = new LineaDetalle(codigo: 'V01', unidad: 'Unid', descripcion: 'Vehículo', cantidad: 1, precio: 15000000, cabys: '4911000000000', vin: 'WVWZZZ3CZWE123456',);

        $arr = $linea->toArray();
        $this->assertSame('WVWZZZ3CZWE123456', $arr['vin']);
    }

    // ---- Exoneracion ----

    public function test_exoneracion_to_array_omits_nulls(): void
    {
        $exo = new Exoneracion(tipodocumento: '04', numerodocumento: 'AL-001', nombreinstitucion: '01', fechaemision: '2024-01-01T08:00:00',);

        $arr = $exo->toArray();

        $this->assertCount(4, $arr);
        $this->assertArrayNotHasKey('articulo', $arr);
        $this->assertArrayNotHasKey('inciso', $arr);
        $this->assertArrayNotHasKey('porcentajecompra', $arr);
    }

    public function test_exoneracion_with_articulo_inciso(): void
    {
        $exo = new Exoneracion(tipodocumento: '02', numerodocumento: 'Ley9635', nombreinstitucion: '01', fechaemision: '2024-01-01T08:00:00', articulo: '5', inciso: 'a', porcentajecompra: 100,);

        $arr = $exo->toArray();
        $this->assertSame('5', $arr['articulo']);
        $this->assertSame('a', $arr['inciso']);
        $this->assertSame(100, $arr['porcentajecompra']);
    }

    // ---- MedioPago ----

    public function test_medio_pago_to_array(): void
    {
        $mp  = new MedioPago(tipomp: 2, montomp: 15000.50);
        $arr = $mp->toArray();

        $this->assertSame('2', $arr['tipomp']);
        $this->assertSame('15000.5', $arr['montomp']);
    }

    public function test_medio_pago_without_monto(): void
    {
        $mp  = new MedioPago(tipomp: 1);
        $arr = $mp->toArray();

        $this->assertSame('1', $arr['tipomp']);
        $this->assertArrayNotHasKey('montomp', $arr);
    }

    // ---- CargoExtra ----

    public function test_cargo_extra_minimal(): void
    {
        $cargo = new CargoExtra(tipocargo: '06', detalle: 'Servicio saloneros 10%', monto: 5000,);

        $arr = $cargo->toArray();

        $this->assertSame('06', $arr['tipocargo']);
        $this->assertSame('Servicio saloneros 10%', $arr['detalle']);
        $this->assertSame(5000.0, $arr['monto']);
        $this->assertArrayNotHasKey('tipoid', $arr);
    }

    public function test_cargo_extra_tercero(): void
    {
        $cargo = new CargoExtra(tipocargo: '04', detalle: 'Cobro tercero', monto: 1000, tipoid: '01', identificacion: '123456789', nombre: 'Juan Pérez',);

        $arr = $cargo->toArray();
        $this->assertSame('01', $arr['tipoid']);
        $this->assertSame('123456789', $arr['identificación']);
        $this->assertSame('Juan Pérez', $arr['nombre']);
    }

    // ---- OtroTexto ----

    public function test_otro_texto_minimal(): void
    {
        $otro = new OtroTexto(valor: 'codigo123', contenido: 'Texto libre');
        $arr  = $otro->toArray();

        $this->assertSame('codigo123', $arr['valor']);
        $this->assertSame('Texto libre', $arr['contenido']);
        $this->assertArrayNotHasKey('atributo', $arr);
    }

    public function test_otro_texto_referencia_ice(): void
    {
        $otro = new OtroTexto(valor: 'Orden', contenido: 'OC-123456', atributo: 'Referencia',);

        $arr = $otro->toArray();
        $this->assertSame('Referencia', $arr['atributo']);
        $this->assertSame('Orden', $arr['valor']);
        $this->assertSame('OC-123456', $arr['contenido']);
    }

    // ---- AltcomResponse ----

    public function test_response_success(): void
    {
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

        $this->assertTrue($response->success);
        $this->assertSame('Aplicado', $response->respuesta);
        $this->assertSame(str_repeat('1', 50), $response->getClave());
        $this->assertSame(str_repeat('2', 20), $response->getConsecutivo());
        $this->assertSame(1, $response->getDoc());
        $this->assertSame(11300.0, $response->getTotal());
        $this->assertSame(1300.0, $response->getImpuesto());
        $this->assertSame('2024-01-01T08:00:00', $response->getFirmado());
        $this->assertSame('<xml/>', $response->getXmlDecoded());
    }

    public function test_response_error(): void
    {
        $response = AltcomResponse::fromArray([
                                                  'respuesta' => 'Documento duplicado',
                                                  'error'     => 1,
                                              ]);

        $this->assertFalse($response->success);
        $this->assertSame('Documento duplicado', $response->respuesta);
        $this->assertNull($response->getClave());
    }

    public function test_response_hacienda_fields(): void
    {
        $response = AltcomResponse::fromArray([
                                                  'respuesta'       => 'Ok',
                                                  'mensaje'         => 'Aceptado',
                                                  'error'           => 0,
                                                  'HaciendaDetalle' => '',
                                              ]);

        $this->assertSame('Aceptado', $response->getMensajeHacienda());
        $this->assertSame('', $response->getHaciendaDetalle());
    }

    public function test_response_to_array(): void
    {
        $data     = ['respuesta' => 'Ok', 'error' => 0];
        $response = AltcomResponse::fromArray($data);

        $this->assertSame($data, $response->toArray());
    }
}
