## Context

El sistema POS cuenta con 18 modelos Eloquent, 14 controladores, 1 Job, 1 Mailable y 20 migraciones. Sin embargo, la cobertura de pruebas es mínima: solo existen los tests de autenticación generados por Laravel Breeze (`AuthenticationTest`, `PasswordResetTest`, etc.) y un `ExampleTest` vacío. No hay model factories para ninguna entidad del dominio excepto `UserFactory`.

Las optimizaciones de rendimiento recientes (`optimizar-rendimiento-pos`) modificaron `DashboardController` (GROUP BY + caché Redis), `PosController` (batch inventory), `User::$with`, e `InventoryController` (eager load), además de agregar índices y constraints en migraciones. Estos cambios no tienen validación automatizada.

El entorno de testing está configurado en `phpunit.xml` con SQLite en memoria (`DB_CONNECTION` por defecto), `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, y `MAIL_MAILER=array` — ideal para pruebas rápidas sin dependencias externas.

## Goals / Non-Goals

**Goals:**
- Crear model factories para todas las entidades del dominio (16 nuevas factories)
- Probar todas las relaciones Eloquent (belongsTo, hasMany, hasOne)
- Probar los flujos de negocio: venta simple, venta compuesta, cierre de caja con cuadre/descuadre, órdenes de compra, recepción y reversión
- Probar el Job de notificación con diferentes escenarios (admins, sin admins, fallos SMTP)
- Probar el middleware de autorización y protección de rutas admin
- Probar edge cases: stock negativo, decimales, compuestos con anidación, inventario duplicado
- Validar que las optimizaciones de rendimiento no introducen regresiones

**Non-Goals:**
- No se modifica código de producción (solo se escriben tests y factories)
- No se prueban vistas Blade (tests funcionales, no de navegador/Dusk)
- No se implementan tests E2E con Selenium o Cypress
- No se modifica `phpunit.xml` ni la configuración de testing
- No se agregan dependencias externas

## Decisions

### 1. DatabaseTransactions en lugar de RefreshDatabase

**Decisión**: Usar el trait `RefreshDatabase` en los tests de feature para resetear la BD entre tests.

**Alternativa considerada**: `DatabaseTransactions`.  
**Razón del rechazo**: Aunque `DatabaseTransactions` es más rápido, `RefreshDatabase` garantiza un estado limpio completo incluyendo datos seedeados. Dado que usamos SQLite en memoria, la diferencia de velocidad es negligible.

### 2. Factories con estados (state methods) para escenarios comunes

**Decisión**: Crear state methods en las factories para escenarios recurrentes (ej. `Product::factory()->composite()`, `Sale::factory()->withDetails(3)`, `CashRegisterSession::factory()->open()` / `->closed()`).

**Alternativa considerada**: Usar helpers en la clase TestCase.  
**Razón del rechazo**: Los state methods son la forma idiomática de Laravel y permiten composición fluida: `Product::factory()->composite()->withComponents(3)->create()`.

### 3. Organización de tests: Unit vs Feature

**Decisión**: 
- `tests/Unit/Models/` → Tests de relaciones, casts, fillable, métodos de negocio en modelos
- `tests/Feature/Controllers/` → Tests de endpoints HTTP, validación, autorización
- `tests/Feature/Jobs/` → Tests del Job de notificación
- `tests/Feature/Flows/` → Tests de flujos completos (venta, cierre de caja, compras)

**Alternativa considerada**: Todo en `tests/Feature/` sin subdirectorios.  
**Razón del rechazo**: Con 30+ tests, la organización plana se vuelve inmanejable. Los subdirectorios coinciden con la estructura de `app/`.

### 4. Factories crean dependencias automáticamente

**Decisión**: Las factories resolverán dependencias con `::factory()` interno. Ej: `Sale::factory()` crea automáticamente `User`, `CashRegisterSession`, `Branch`, `CashRegister` si no se especifican.

**Alternativa considerada**: Requerir que el test cree todas las dependencias manualmente.  
**Razón del rechazo**: Haría los tests verbosos y frágiles. Las factories autosuficientes permiten tests concisos como `Sale::factory()->create()`.

### 5. No usar mocks para modelos Eloquent

**Decisión**: Usar la base de datos real (SQLite en memoria) en lugar de mocks para Eloquent.

**Alternativa considerada**: `Mockery::mock(Product::class)`.  
**Razón del rechazo**: Mockear Eloquent es frágil y no prueba las queries reales. SQLite en memoria es rápido y detecta problemas reales de relaciones, constraints, y casts.

## Risks / Trade-offs

- **[Riesgo] SQLite vs MySQL**: SQLite no soporta todas las features de MySQL (ENUM, algunas constraints). Las migraciones usan `enum('type', ['sale', 'waste'])` que SQLite trata como string. → Mitigación: Los tests validan lógica de negocio, no sintaxis SQL específica. Para validación de índices se usan tests separados con Sail/MySQL.
- **[Riesgo] Factories anidadas pueden crear demasiados registros**: Una `Sale::factory()` crea User → Role, CashRegisterSession → CashRegister → Branch. → Mitigación: Las factories aceptan overrides para reutilizar instancias existentes (`Sale::factory()->for($user)->for($session)`).
- **[Trade-off] Tiempo de ejecución**: Con 30+ tests usando RefreshDatabase, la suite puede tomar 10-30 segundos. → Aceptable para el volumen actual del proyecto (sub-100 tests).
- **[Trade-off] Factories no cubren todas las variaciones**: Las factories cubren el caso feliz por defecto. Los edge cases se manejan en los tests específicos usando `state()` o overrides manuales.

## Open Questions

- ¿Se deben probar los emails reales con `Mail::fake()` o verificar solo el despacho del Job? → Se usará `Mail::fake()` en tests de Job y `Bus::fake()` en tests de controlador.
- ¿Vale la pena probar `processInventory()` de forma aislada (unit test) o solo a través del endpoint (feature test)? → Ambas: unit test para la lógica de `collectProductIds` + feature test para el flujo completo.
