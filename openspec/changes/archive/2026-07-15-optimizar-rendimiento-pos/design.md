## Context

El sistema POS actual opera sin índices en las columnas de mayor frecuencia de consulta, lo que obliga a MySQL a realizar full table scans en cada operación. Redis está disponible en el contenedor pero no se utiliza para caché de sesiones ni de datos. Varios controladores presentan patrones N+1 que multiplican las consultas por cada registro procesado.

**Stack**: PHP 8.3 + Laravel 13 + MySQL 8.4 + Redis Alpine + Docker/Sail. Las migraciones son la única vía para modificar la base de datos. El entorno sigue en desarrollo (`APP_ENV=local`) — no se modifica esta configuración.

## Goals / Non-Goals

**Goals:**
- Reducir las consultas del dashboard de 10 a ~3 mediante GROUP BY y caché Redis
- Eliminar el N+1 recursivo en `processInventory()` usando consultas batch
- Eliminar las 3 consultas extra por página por `Auth::user()->role` mediante eager loading en el modelo
- Agregar índices que aceleren las queries más frecuentes (sales por fecha/tipo, sesiones activas, inventario por producto/sucursal)
- Implementar caché Redis con TTL de 60s para estadísticas del dashboard

**Non-Goals:**
- No se modifica `APP_ENV` ni `APP_DEBUG` (el sistema sigue en desarrollo)
- No se migran `SESSION_DRIVER`, `QUEUE_CONNECTION` a Redis (solo `CACHE_STORE`)
- No se modifica la arquitectura MVC ni se introducen nuevas dependencias
- No se tocan vistas Blade ni rutas
- No se modifica el frontend (Alpine.js, Tailwind ya son eficientes)

## Decisions

### 1. Índices compuestos en lugar de índices individuales por columna

**Decisión**: Crear índices compuestos donde el patrón de consulta lo justifica. Para `sales`, un índice compuesto `(type, created_at)` cubre tanto las queries por tipo como las queries por tipo+fecha.

**Alternativa considerada**: Índices separados `type` y `created_at`.  
**Razón del rechazo**: MySQL puede usar el prefijo izquierdo de un índice compuesto. `(type, created_at)` sirve tanto para `WHERE type = ?` como para `WHERE type = ? AND DATE(created_at) = ?`. Ocupa menos espacio y es más eficiente que dos índices separados.

### 2. Índice UNIQUE en inventories(product_id, branch_id)

**Decisión**: Agregar una restricción `UNIQUE` en `(product_id, branch_id)` en la tabla `inventories`.

**Alternativa considerada**: Solo un índice simple.  
**Razón del rechazo**: `firstOrCreate()` ya asume que la combinación es única. Sin la restricción UNIQUE, queries concurrentes pueden crear duplicados. El índice acelera `firstOrCreate` y `where()->first()` y garantiza integridad de datos.

### 3. Consulta GROUP BY en dashboard en lugar del loop for

**Decisión**: Reemplazar el `for ($i = 6; $i >= 0; $i--)` en `DashboardController` por una sola query con `GROUP BY DATE(created_at)` y un `WHERE created_at BETWEEN ? AND ?`.

**Alternativa considerada**: Mantener el loop y agregar caché.  
**Razón del rechazo**: El caché reduce el impacto pero no elimina la ineficiencia. Una query agrupada resuelve el problema de raíz y es trivial de implementar.

### 4. Consultas batch en processInventory() en lugar de N+1 recursivo

**Decisión**: Recolectar todos los IDs de productos primero (incluyendo componentes de compuestos recursivamente), luego hacer una sola consulta `Product::whereIn('id', $ids)->with('components')`, y una sola consulta batch de inventario.

**Alternativa considerada**: Usar `Product::with('components')->findMany($ids)` una vez por nivel de recursión.  
**Razón del rechazo**: Sigue siendo una consulta por nivel. La solución batch carga todos los productos y componentes en una sola consulta plana, independientemente de la profundidad de anidamiento.

### 5. Eager loading global de `role` en User

**Decisión**: Agregar `protected $with = ['role']` en el modelo `User`.

**Alternativa considerada**: Usar `Auth::user()->loadMissing('role')` en cada lugar que accede a role.  
**Razón del rechazo**: Requiere tocar 3+ archivos (middleware, vista nav, posibles futuros usos) y es frágil. `$with` lo resuelve globalmente. El overhead de cargar `role` siempre es mínimo (es una relación belongsTo con FK en `users`).

### 6. Caché Redis para estadísticas del dashboard

**Decisión**: Usar `Cache::remember('dashboard:stats', 60, fn() => ...)` para las 3 queries del dashboard. El TTL de 60s da frescura aceptable para un dashboard administrativo.

**Alternativa considerada**: Usar el driver `database` para caché (sin cambiar a Redis).  
**Razón del rechazo**: Sumaría más carga a MySQL. Redis ya está corriendo en el contenedor sin uso. `CACHE_STORE=redis` aprovecha infraestructura existente sin costo adicional.

### 7. Una sola migración para todos los índices

**Decisión**: Crear un único archivo de migración `add_performance_indexes` que contenga todos los índices y constraints.

**Alternativa considerada**: Una migración por tabla.  
**Razón del rechazo**: Los índices están lógicamente relacionados (todos son optimizaciones de rendimiento). Una migración única es más fácil de aplicar y revertir.

## Risks / Trade-offs

- **[Riesgo] La migración de índices puede bloquear tablas en producción** → En desarrollo/local esto no es problema. Si se despliega en producción, se debe ejecutar en horario de baja actividad. Mitigación: los índices se crean con `algorithm => 'inplace'` donde MySQL lo soporte.
- **[Riesgo] El índice UNIQUE en inventories puede fallar si ya hay duplicados** → La migración debe verificar y limpiar duplicados antes de aplicar la constraint. Mitigación: agregar un paso de limpieza en la migración (`DELETE t1 FROM inventories t1 INNER JOIN inventories t2 ...`).
- **[Trade-off] `$with = ['role']` carga role incluso cuando no se necesita** → El overhead es insignificante (una consulta JOIN simple en una tabla de roles con pocos registros). El beneficio de eliminar 3 queries por página lo supera ampliamente.
- **[Trade-off] Caché de 60s significa que el dashboard puede mostrar datos con hasta 1 minuto de desfase** → Aceptable para un dashboard administrativo. Si se requiere tiempo real, se puede reducir el TTL o invalidar el caché en eventos de venta.

## Open Questions

- ¿Se justifica agregar también índices en `sale_details(sale_id)`, `payments(sale_id)` y `product_components(product_id)`? → Probablemente ya existen por ser FK (Laravel los crea con `foreignId`). Se verificará durante la implementación.
