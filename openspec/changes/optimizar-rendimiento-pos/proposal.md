## Why

El sistema POS presenta lentitud en operaciones críticas como la carga del dashboard, el procesamiento de ventas y la apertura del módulo POS. El análisis de rendimiento reveló tres causas principales: ausencia de índices en columnas de alta frecuencia de consulta (`sales.type`, `sales.created_at`, `cash_register_sessions.status`, `inventories.product_id + branch_id`), múltiples consultas N+1 en el dashboard, el procesamiento de inventario recursivo, y el middleware de autenticación, y falta de caché para datos consultados repetidamente. Resolver esto mejorará significativamente la experiencia de usuario sin modificar la arquitectura existente.

## What Changes

- **Nuevas migraciones de base de datos** con índices optimizados para las tablas `sales`, `cash_register_sessions` e `inventories`
- **Refactorización de consultas en `DashboardController`**: reemplazar el loop de 7 queries individuales por una consulta agrupada con `GROUP BY`
- **Refactorización de `processInventory()` en `PosController`**: realizar consultas batch en lugar de N+1 recursivo
- **Carga eager de la relación `role`** en el modelo `User` mediante un atributo `$with` o scope global para eliminar las 3 consultas extra por página
- **Carga eager de `unitOfMeasure`** en el `InventoryController` para eliminar N+1 en la vista de inventario
- **Caché básico con Redis** para los datos del dashboard (stats de ventas, alertas de inventario) con TTL de 60 segundos

## Capabilities

### New Capabilities
- `indices-base-datos`: Creación de índices compuestos y únicos en las tablas `sales`, `cash_register_sessions` e `inventories` para acelerar las consultas más frecuentes del sistema
- `optimizacion-consultas`: Refactorización de consultas N+1 en `DashboardController`, `PosController::processInventory()`, `InventoryController`, y carga eager de `role` en el modelo `User`
- `cache-dashboard`: Implementación de caché con Redis para las estadísticas del dashboard y datos consultados frecuentemente

### Modified Capabilities
<!-- No se modifican capacidades existentes; son optimizaciones internas -->

## Impact

- **Migraciones nuevas**: 1 archivo de migración para los índices (`sales`, `cash_register_sessions`, `inventories`)
- **Controladores modificados**: `DashboardController` (loop de chart → GROUP BY), `PosController` (processInventory batch), `InventoryController` (eager load unitOfMeasure)
- **Modelos modificados**: `User` (agregar `$with = ['role']` o scope global)
- **Archivos nuevos**: `config/cache.php` o actualización de `.env` para Redis si no está ya configurado; verificación de que `CACHE_STORE=redis`
- **Dependencias**: Ninguna nueva externa; Redis ya está disponible en el contenedor
- **Sin cambios en**: Vistas Blade, rutas, middleware, Mailable, Jobs
