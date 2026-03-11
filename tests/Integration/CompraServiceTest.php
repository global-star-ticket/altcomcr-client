<?php

namespace Altcomcr\Client\Tests\Integration;

use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Enums\Moneda;
use Altcomcr\Client\Enums\TipoIdentificacion;
use Altcomcr\Client\Enums\TipoPago;
use Altcomcr\Client\Enums\UnidadServicio;
use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class CompraServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    public function test_emitir_factura_compra_extranjero(): void
    {
        $documento = static::uniqueDocumento('FC');
        $fecha     = date('Y-m-d H:i:s');
        $tipodoc   = '16'; // Extranjero

        $proveedor = [
            'prov_cedula'    => 'EXTTEST12345',
            'prov_tipo'      => TipoIdentificacion::Extranjero,
            'prov_nombre'    => 'Proveedor Extranjero Test LLC',
            'prov_email'     => 'test@proveedor-externo.com',
            'prov_direccion' => 'Test City, Test Country',
        ];

        $detalle = [
            new LineaDetalle(codigo: 'EXT-01', unidad: UnidadServicio::ServiciosProfesionales, descripcion: 'Servicio de hosting prueba', cantidad: 1, precio: 50, cabys: '8314100000100', impuesto: 13)
        ];

        $response = static::altcom()
            ->compras()
            ->emitir(documento: $documento, fecha: $fecha, tipodoc: $tipodoc, moneda: Moneda::fromIso('USD'), mediopago: MedioPagoTipo::Transferencia, tipopago: TipoPago::Contado, proveedor: $proveedor, detalle: $detalle);

        $this->assertTrue($response->success);
        $this->assertSame('Aplicado', $response->respuesta);
        $this->assertNotEmpty($response->getClave());
    }
}
