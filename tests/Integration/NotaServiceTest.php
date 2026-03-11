<?php

namespace Altcomcr\Client\Tests\Integration;

use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\Enums\TipoNota;
use Altcomcr\Client\Enums\UnidadMedida;
use Altcomcr\Client\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class NotaServiceTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        static::requiresCredentials();
    }

    private function requiresFacturaContado(): string
    {
        $clave = FacturaServiceTest::$claveFacturaContado;

        if (empty($clave)) {
            $this->markTestSkipped('No hay factura de contado emitida previamente. Ejecutar FacturaServiceTest primero.');
        }

        return $clave;
    }

    public function test_nota_credito_por_monto(): void
    {
        $clavedoc = $this->requiresFacturaContado();
        $documento = static::uniqueDocumento('NCM');

        $response = static::altcom()->notas()->emitirPorMonto(
            clavedoc: $clavedoc,
            documento: $documento,
            tipo: TipoNota::Credito,
            detalle: 'Nota de crédito por monto - prueba automatizada',
            monto_prod: 5000,
            impuesto_prod: 13,
        );

        $this->assertTrue($response->success);
        $this->assertSame('Aplicado', $response->respuesta);
        $this->assertNotEmpty($response->getClave());
    }

    public function test_nota_credito_por_detalle(): void
    {
        $clavedoc = $this->requiresFacturaContado();
        $documento = static::uniqueDocumento('NCD');

        $detalle = [
            new LineaDetalle(
                codigo: 'PROD-01',
                unidad: UnidadMedida::Unidad,
                descripcion: 'Devolución producto de prueba',
                cantidad: 1,
                precio: 5000,
                cabys: '2350201000000',
                impuesto: 13,
            ),
        ];

        $response = static::altcom()->notas()->emitirPorDetalle(
            clavedoc: $clavedoc,
            documento: $documento,
            tipo: TipoNota::Credito,
            detalle: $detalle,
            observacion: 'Devolución por prueba automatizada',
        );

        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->getClave());
    }

    public function test_nota_debito_por_monto(): void
    {
        $clavedoc = $this->requiresFacturaContado();
        $documento = static::uniqueDocumento('NDM');

        $response = static::altcom()->notas()->emitirPorMonto(
            clavedoc: $clavedoc,
            documento: $documento,
            tipo: TipoNota::Debito,
            detalle: 'Nota de débito por monto - prueba automatizada',
            monto_serv: 1000,
            impuesto_serv: 13,
        );

        $this->assertTrue($response->success);
        $this->assertNotEmpty($response->getClave());
    }
}
