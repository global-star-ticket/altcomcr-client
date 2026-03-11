# Altcom PHP Client

Cliente PHP para la API de facturación electrónica de [Altcom Costa Rica](https://www.altcomcr.net).

**Framework-agnostic** - funciona con cualquier proyecto PHP 8.2+. Incluye integración opcional para Laravel 11/12.

Diseñado para escenarios **multi-tenant** donde múltiples emisores usan la API con credenciales diferentes.

## Instalación

```bash
composer require altcomcr/client
```

## Uso (PHP puro)

```php
use Altcomcr\Client\Altcom;

$altcom = new Altcom(
    usuario: 'email@ejemplo.com',
    clave: 'mi-contraseña',
    cedula: '123456789',
    sandbox: true, // false para producción
);

// O con el factory estático
$altcom = Altcom::make(
    usuario: 'email@ejemplo.com',
    clave: 'mi-contraseña',
    cedula: '123456789',
);
```

> La contraseña se envía automáticamente como digestión SHA1 por seguridad.

### Parámetros opcionales del constructor

| Parámetro | Default | Descripción |
|-----------|---------|-------------|
| `sucursal` | `'001'` | Código de sucursal |
| `terminal` | `'00001'` | Código de terminal |
| `actividad` | `''` | Código de actividad económica |
| `sandbox` | `true` | `true` = sandbox, `false` = producción |
| `timeout` | `30` | Timeout HTTP en segundos |
| `retries` | `3` | Reintentos en caso de fallo de conexión |
| `retryDelay` | `100` | Delay entre reintentos (ms) |
| `baseUrl` | `null` | URL personalizada (sobreescribe sandbox) |

### Multi-tenant: diferentes emisores

```php
$emisorA = Altcom::make(
    usuario: $userA->altcom_email,
    clave: $userA->altcom_password,
    cedula: $userA->cedula,
);

$emisorB = Altcom::make(
    usuario: $userB->altcom_email,
    clave: $userB->altcom_password,
    cedula: $userB->cedula,
    sucursal: '002',
);

$emisorA->facturas()->emitir(...);
$emisorB->facturas()->emitir(...);
```

## Integración con Laravel

El ServiceProvider se auto-registra. Publicar la configuración:

```bash
php artisan vendor:publish --tag=altcom-config
```

El archivo `config/altcom.php` contiene solo configuración de infraestructura:

```env
ALTCOM_SANDBOX=true
ALTCOM_TIMEOUT=30
ALTCOM_RETRIES=3
```

En Laravel se puede inyectar `AltcomFactory` para crear instancias con la config precargada:

```php
use Altcomcr\Client\AltcomFactory;

class FacturacionController extends Controller
{
    public function emitir(AltcomFactory $factory)
    {
        $altcom = $factory->make(
            usuario: $user->altcom_email,
            clave: $user->altcom_password,
            cedula: $user->cedula,
        );

        $response = $altcom->facturas()->emitir(...);
    }
}
```

## Emitir Factura

```php
use Altcomcr\Client\DTOs\LineaDetalle;
use Altcomcr\Client\Enums\MedioPagoTipo;
use Altcomcr\Client\Enums\Moneda;
use Altcomcr\Client\Enums\TipoIdentificacion;
use Altcomcr\Client\Enums\TipoPago;
use Altcomcr\Client\Enums\UnidadServicio;

$response = $altcom->facturas()->emitir(
    documento: 'FAC-001',
    moneda: Moneda::fromIso('CRC'),
    tipopago: TipoPago::Contado,
    detalle: [
        new LineaDetalle(
            codigo: '001',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio de consultoría',
            cantidad: 1,
            precio: 50000,
            cabys: '8531100000100',
            impuesto: 13,
        ),
    ],
    opciones: [
        'cli_cedula' => '123456789',
        'cli_tipo' => TipoIdentificacion::Fisico,
        'cli_nombre' => 'Juan Pérez',
        'cli_codact' => '620101',
        'mediopago' => MedioPagoTipo::Efectivo,
    ],
);

// Acceder a la respuesta
$response->getClave();       // Clave de 50 dígitos
$response->getConsecutivo(); // Consecutivo de 20 dígitos
$response->getTotal();       // Total de la factura
$response->getXmlDecoded();  // XML decodificado de Base64
```

## Nota de Crédito por Monto

```php
use Altcomcr\Client\Enums\TipoNota;

$response = $altcom->notas()->emitirPorMonto(
    clavedoc: '50628012400310123456700100001010000000001199999999',
    documento: 'NC-001',
    tipo: TipoNota::Credito,
    detalle: 'Devolución parcial de servicio',
    monto_serv: 10000,
    impuesto_serv: 13,
);
```

## Nota de Crédito por Detalle

```php
$response = $altcom->notas()->emitirPorDetalle(
    clavedoc: '50628012400310123456700100001010000000001199999999',
    documento: 'NC-002',
    tipo: TipoNota::Credito,
    detalle: [
        new LineaDetalle(
            codigo: '001',
            unidad: UnidadMedida::Unidad,
            descripcion: 'Producto devuelto',
            cantidad: 1,
            precio: 5000,
            cabys: '2350201000000',
            impuesto: 13,
        ),
    ],
);
```

## Consultar respuesta de Hacienda

```php
$response = $altcom->consultas()->consultarHacienda(
    clavedoc: '50628012400310123456700100001010000000001199999999'
);

$response->getMensajeHacienda();  // "Aceptado" o "Rechazado"
$response->getHaciendaDetalle(); // Detalle del rechazo si aplica
```

## Consulta Interna en Altcom

```php
$response = $altcom->consultas()->consultarInterno(documento: 'FAC-001');
```

## Notificar Gasto / Compra recibida

```php
use Altcomcr\Client\Enums\IvaCondicion;

$xmlBase64 = base64_encode($xmlDelProveedor);

$response = $altcom->gastos()->notificar(
    xml: $xmlBase64,
    aceptado: 1, // 1=Aceptado, 3=Rechazado
    iva_condicion: IvaCondicion::GeneraCreditoIva,
);
```

## Factura de Compra

```php
$response = $altcom->compras()->emitir(
    documento: 'REC-PROV-001',
    fecha: '2025-01-15 10:30:00',
    tipodoc: '16',
    moneda: Moneda::fromIso('USD'),
    mediopago: MedioPagoTipo::Transferencia,
    tipopago: TipoPago::Contado,
    proveedor: [
        'prov_cedula' => 'PASS12345',
        'prov_tipo' => TipoIdentificacion::Extranjero,
        'prov_nombre' => 'Proveedor Internacional LLC',
        'prov_email' => 'proveedor@ejemplo.com',
        'prov_direccion' => 'Miami, FL, USA',
    ],
    detalle: [
        new LineaDetalle(
            codigo: 'SERV-01',
            unidad: UnidadServicio::ServiciosProfesionales,
            descripcion: 'Servicio de hosting anual',
            cantidad: 1,
            precio: 120,
            cabys: '8314100000100',
            impuesto: 13,
        ),
    ],
);
```

## Recibo Electrónico de Pago

```php
$response = $altcom->recibosPago()->emitir(
    clavedoc: '50628012400310123456700100001010000000001199999999',
    documento: 'PAG-001',
    monto: 25000,
    mediopago: MedioPagoTipo::Transferencia,
    detalle: 'Pago parcial - cuota 1',
);
```

## Anular Documento

```php
$response = $altcom->anulacion()->anular(
    clavedoc: '50628012400310123456700100001010000000001199999999'
);
```

## Consultar Saldo y Llave Criptográfica

```php
$response = $altcom->consultas()->saldo();
// $response->data['vence'], $response->data['dias']

$response = $altcom->consultas()->llave();
// $response->data['vence'], $response->data['actividades']
```

## Medios de Pago Múltiples

```php
use Altcomcr\Client\DTOs\MedioPago;
use Altcomcr\Client\Enums\MedioPagoTipo;

$response = $altcom->facturas()->emitir(
    documento: 'FAC-002',
    moneda: Moneda::fromIso('CRC'),
    tipopago: TipoPago::Contado,
    detalle: [$linea],
    opciones: [
        'mediopago' => [
            new MedioPago(tipomp: MedioPagoTipo::Tarjeta, montomp: 15000),
            new MedioPago(tipomp: MedioPagoTipo::Transferencia, montomp: 10000),
            new MedioPago(tipomp: MedioPagoTipo::Efectivo), // resto automático
        ],
    ],
);
```

## Exoneraciones

```php
use Altcomcr\Client\DTOs\Exoneracion;
use Altcomcr\Client\Enums\InstitucionExoneracion;
use Altcomcr\Client\Enums\TipoDocumentoExoneracion;

$linea = new LineaDetalle(
    codigo: '001',
    unidad: UnidadServicio::ServiciosProfesionales,
    descripcion: 'Servicio exonerado',
    cantidad: 1,
    precio: 100000,
    cabys: '8531100000100',
    impuesto: 13,
    exonerado: new Exoneracion(
        tipodocumento: TipoDocumentoExoneracion::ExencionesDGH,
        numerodocumento: 'AL-000001-2024',
        nombreinstitucion: InstitucionExoneracion::MinisterioHacienda,
        fechaemision: '2024-01-15T08:00:00',
        porcentajecompra: 13,
    ),
);
```

## Cargos Extra y Nodo Otros

```php
use Altcomcr\Client\DTOs\CargoExtra;
use Altcomcr\Client\DTOs\OtroTexto;
use Altcomcr\Client\Enums\TipoCargo;

// Cargo extra
$cargos = [
    new CargoExtra(tipocargo: TipoCargo::ServicioSaloneros, detalle: 'Servicio de mesa 10%', monto: 5000),
];

// Caso ICE - Orden de compra
$otros = [
    new OtroTexto(valor: 'Orden', contenido: '123456', atributo: 'Referencia'),
];

// Referencia a documento rechazado
$otros = [
    new OtroTexto(
        valor: '50628012400310123456700100001010000000001199999999',
        contenido: 'Sustituye documento rechazado',
        atributo: 'Referencia',
    ),
];
```

## Manejo de Errores

```php
use Altcomcr\Client\Exceptions\AltcomApiException;
use Altcomcr\Client\Exceptions\AltcomConnectionException;

try {
    $response = $altcom->facturas()->emitir(...);
} catch (AltcomApiException $e) {
    // Error de la API (validación, datos incorrectos, etc.)
    $e->getMessage();         // Detalle del error
    $e->getAltcomResponse();  // JSON completo de la respuesta
} catch (AltcomConnectionException $e) {
    // Error de conexión (timeout, red, etc.)
    $e->getMessage();
}
```

## Enums Disponibles

- `Moneda` - Colones (CRC), Dólares (USD), Euros (EUR) + helpers `fromIso()` / `iso()`
- `TipoPago` - Contado, Crédito, Consignación, Apartado, etc.
- `MedioPagoTipo` - Efectivo, Tarjeta, Cheque, Transferencia, Terceros, SinpeMovil
- `TipoIdentificacion` - Físico, Jurídico, DIMEX, NITE, Extranjero
- `TipoNota` - Credito, Debito
- `Destino` - Tiquete, Factura, Exportación
- `UnidadMedida` - Metro, Kilogramo, Unidad, Litro, Gramo, etc. (bienes, CABYS 0-4)
- `UnidadServicio` - ServiciosProfesionales, ServiciosTecnicos, Hora, Día, etc. (CABYS 5-9)
- `TipoCargo` - ContribuciónParafiscal, TimbreCruzRoja, ServicioSaloneros, etc.
- `TipoDescuento` - Regalía, Bonificación, Comercial, Promocional, etc.
- `TipoDocumentoExoneracion` - ComprasAutorizadas, ExencionesDGH, ZonaFranca, etc.
- `InstitucionExoneracion` - MinisterioHacienda, CruzRoja, Bomberos, EARTH, etc.
- `IvaCondicion` - GeneraCreditoIva, CreditoParcial, BienesCapital, etc.

### Resolución transparente

Todos los enums se resuelven automáticamente al pasar a servicios y DTOs. No es necesario llamar `->value`:

```php
// Todas estas formas son equivalentes:
$altcom->facturas()->emitir(
    moneda: Moneda::Colones,         // enum directo
    moneda: Moneda::fromIso('CRC'),  // helper ISO
    moneda: 1,                        // valor crudo (compatibilidad)
);

// resolve() permite convertir desde texto, nombre del case o valor crudo:
Moneda::resolve('CRC');       // 1 (ISO)
Moneda::resolve('Colones');   // 1 (nombre del case)
Moneda::resolve(1);           // 1 (valor crudo)
TipoPago::resolve('Contado'); // 1
TipoPago::resolve(1);         // 1
```

### Helper de Moneda (ISO 4217)

```php
use Altcomcr\Client\Enums\Moneda;

Moneda::fromIso('CRC'); // Moneda::Colones
Moneda::fromIso('usd'); // Moneda::Dolares (case-insensitive)
Moneda::Colones->iso(); // 'CRC'

Moneda::fromIso('GBP'); // throws \ValueError
```

## Tests

El paquete incluye tests unitarios y de integración contra el sandbox de Altcom.

### Requisitos

```bash
composer install
```

### Configurar credenciales

Copiar `.env.example` a `.env` y llenar con credenciales válidas del sandbox:

```bash
cp .env.example .env
```

```env
ALTCOM_TEST_USUARIO=tu-email@ejemplo.com
ALTCOM_TEST_CLAVE=tu-contraseña
ALTCOM_TEST_CEDULA=tu-cedula-emisor
ALTCOM_TEST_SUCURSAL=001
ALTCOM_TEST_TERMINAL=00001
ALTCOM_TEST_ACTIVIDAD=tu-codigo-actividad
```

> El archivo `.env` está excluido del repositorio vía `.gitignore`.

### Ejecutar tests

```bash
# Solo unitarios (no requieren credenciales ni conexión)
./vendor/bin/phpunit --testsuite unit

# Solo integración contra sandbox (requiere .env configurado)
./vendor/bin/phpunit --testsuite integration

# Todos los tests
./vendor/bin/phpunit
```

### Estructura de tests

| Suite | Descripción |
|-------|-------------|
| `unit` | Valida DTOs, payloads, constructores, enums. Sin red. |
| `integration` | Emite facturas, notas, compras, recibos y anulaciones contra el sandbox real. |

Los tests de integración se ejecutan en orden: `FacturaServiceTest` emite documentos y guarda las claves. Los demás tests (notas, consultas, recibos, anulación) usan esas claves como referencia. Si no hay credenciales configuradas, los tests de integración se saltan automáticamente.

## Licencia

MIT
