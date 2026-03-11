<?php

namespace Altcomcr\Client\Tests\Integration;

use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class ReciboPagoServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    public function test_emitir_recibo_pago(): void
    {
        $clave = FacturaServiceTest::$claveFacturaCredito;

        if (empty($clave)) {
            $this->markTestSkipped('No hay factura a crédito emitida previamente.');
        }

        $documento = static::uniqueDocumento('PAG');

        $response = static::altcom()
            ->recibosPago()
            ->emitir(clavedoc: $clave, documento: $documento, monto: 10000, mediopago: MedioPagoTipo::Transferencia, detalle: 'Abono parcial - prueba automatizada');

        $this->assertTrue($response->success);
        $this->assertSame('Aplicado', $response->respuesta);
        $this->assertNotEmpty($response->getClave());
    }
}
