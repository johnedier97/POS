## Why

El sistema POS carece de cobertura de pruebas más allá de los tests de autenticación generados por Laravel Breeze. Sin tests unitarios ni de integración para los flujos críticos (ventas, cierre de caja, inventario, órdenes de compra), los errores de regresión solo se detectan en producción. Las optimizaciones recientes de rendimiento (índices, eager loading, caché Redis, proceso batch de inventario) introdujeron cambios sustanciales en el núcleo sin validación automatizada. Se requiere una suite de pruebas que cubra modelos, controladores, jobs y flujos de negocio para garantizar estabilidad y detectar bugs tempranamente.

## What Changes

- **Factories para todos los modelos del dominio**: Crear model factories para `Role`, `Branch`, `CashRegister`, `Product`, `Inventory`, `Customer`, `Supplier`, `Sale`, `Payment`, etc. — actualmente solo existe `UserFactory`
- **Pruebas unitarias de modelos**: Validar relaciones, casts, fillable attributes, y lógica de negocio en modelos (`CashRegisterSession::isOpen()`, `Product.is_composite`, cálculo de subtotales en `SaleDetail`)
- **Pruebas de feature para flujos críticos**: Dashboard (cache, stats, gráfico 7 días), ventas POS (simples, compuestos, split de pagos, merma), cierre de caja (cuadre, descuadre, job de notificación), órdenes de compra (crear, recibir, revertir), inventario (listado, eager loading, filtros)
- **Pruebas del Job de notificación**: Verificar que `SendCashRegisterClosedMail` envía correos solo a admins, maneja fallos individuales, y no falla sin admins
- **Pruebas de middleware y autorización**: Validar que `CheckRole` bloquea accesos no autorizados y permite admin
- **Pruebas de edge cases**: Stock negativo, productos compuestos con anidación profunda, inventario duplicado (pre-índice UNIQUE), ventas con sesión cerrada, balance con decimales
- **Pruebas de regresión para optimizaciones**: Confirmar que `Cache::remember` funciona, que las consultas batch no rompen el inventario, y que los índices se crean/revierten correctamente

## Capabilities

### New Capabilities
- `factories-modelos`: Creación de model factories para todas las entidades del dominio POS (`Role`, `Branch`, `CashRegister`, `CashRegisterSession`, `Product`, `ProductComponent`, `UnitOfMeasure`, `Inventory`, `Customer`, `Supplier`, `Sale`, `SaleDetail`, `Payment`, `PaymentMethod`, `PurchaseOrder`, `PurchaseOrderDetail`, `Setting`)
- `tests-unitarios-modelos`: Pruebas unitarias de modelos Eloquent (relaciones, casts, atributos fillable, scopes, métodos de negocio)
- `tests-feature-flujos`: Pruebas de integración/feature para los flujos de negocio principales (venta POS, cierre de caja, órdenes de compra, inventario, dashboard)
- `tests-job-notificacion`: Pruebas del Job `SendCashRegisterClosedMail` (envío a admins, tolerancia a fallos, sin admins)
- `tests-autorizacion`: Pruebas del middleware `CheckRole` y protección de rutas admin
- `tests-edge-cases`: Pruebas de casos límite (stock negativo, compuestos recursivos, decimales, concurrencia, descuadre)
- `tests-regresion-optimizaciones`: Pruebas que validan que las optimizaciones de rendimiento no rompen funcionalidad existente

### Modified Capabilities
<!-- No se modifican capacidades existentes -->

## Impact

- **Archivos nuevos**: ~20 model factories en `database/factories/`, ~15 archivos de prueba en `tests/Unit/` y `tests/Feature/`
- **Archivos sin cambios**: Modelos, controladores, vistas, rutas, migraciones existentes
- **Migraciones**: No se requieren nuevas migraciones
- **Dependencias**: Sin nuevas dependencias externas; PHPUnit ya está en `composer.json`
- **Seeder**: Se puede extender `DatabaseSeeder` para usar las nuevas factories en desarrollo
