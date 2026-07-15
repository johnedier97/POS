## 1. Migración de índices de base de datos

- [x] 1.1 Crear migración `add_performance_indexes` con `./vendor/bin/sail artisan make:migration add_performance_indexes`
- [x] 1.2 Agregar índice compuesto `(type, created_at)` en tabla `sales` con algoritmo `inplace` donde soportado
- [x] 1.3 Agregar índice en columna `status` de tabla `cash_register_sessions`
- [x] 1.4 Agregar restricción `UNIQUE` en `(product_id, branch_id)` de tabla `inventories`, incluyendo limpieza previa de duplicados con `DELETE t1 FROM inventories t1 INNER JOIN inventories t2 ...`
- [x] 1.5 Ejecutar migración con `./vendor/bin/sail artisan migrate` y verificar que se completa sin errores
- [x] 1.6 Verificar índices creados con `./vendor/bin/sail artisan db:show --indexes` o `./vendor/bin/sail mysql -e "SHOW INDEXES FROM sales; SHOW INDEXES FROM cash_register_sessions; SHOW INDEXES FROM inventories;"`

## 2. Optimización del DashboardController

- [x] 2.1 Reemplazar el loop `for ($i = 6; $i >= 0; $i--)` por una sola consulta con `->where('type', 'sale')->whereBetween('created_at', [$start, $end])->groupBy(DB::raw('DATE(created_at)'))->selectRaw('DATE(created_at) as date, SUM(total) as total')->pluck('total', 'date')`
- [x] 2.2 Completar los días sin ventas con valor `0` en el array `$salesData` desde PHP
- [x] 2.3 Verificar sintaxis: `./vendor/bin/sail php -l app/Http/Controllers/DashboardController.php`
- [x] 2.4 Probar carga del dashboard y confirmar que los datos del gráfico son correctos para los 7 días

## 3. Refactorización de processInventory en PosController

- [x] 3.1 Crear método privado `collectProductIds($productId, $quantity)` que recorra recursivamente los componentes de productos compuestos y retorne un array asociativo de `[product_id => total_quantity]`
- [x] 3.2 Modificar `processInventory()` para que reciba el array completo de `$productConsumption` en lugar de procesar item por item
- [x] 3.3 Realizar una sola consulta `Product::whereIn('id', array_keys($consumption))->with('components')->get()` para cargar todos los productos
- [x] 3.4 Realizar una consulta batch para inventario con `Inventory::whereIn('product_id', $ids)->where('branch_id', $branchId)->get()->keyBy('product_id')` y actualizar stocks en memoria antes de un `update()` batch
- [x] 3.5 Modificar el `foreach` en `PosController::store()` (línea 69) para recolectar los items primero y llamar a `processInventory` una sola vez
- [x] 3.6 Verificar sintaxis: `./vendor/bin/sail php -l app/Http/Controllers/PosController.php`
- [x] 3.7 Procesar venta de prueba con productos simples y compuestos para verificar que el inventario se actualiza correctamente

## 4. Carga eager de role en modelo User

- [x] 4.1 Agregar `protected $with = ['role'];` en `app/Models/User.php`
- [x] 4.2 Verificar que el middleware `CheckRole` (`app/Http/Middleware/CheckRole.php`) no lanza error cuando `$request->user()->role` ya está cargado
- [x] 4.3 Verificar sintaxis: `./vendor/bin/sail php -l app/Models/User.php`
- [x] 4.4 Navegar por varias páginas del sistema (dashboard, POS, inventario) y confirmar con Laravel Debugbar o Telescope que no se generan consultas lazy-load para `role`

## 5. Carga eager de unitOfMeasure en InventoryController

- [x] 5.1 Modificar `InventoryController::index()` para cambiar `->with(['product', 'branch'])` por `->with(['product.unitOfMeasure', 'branch'])`
- [x] 5.2 Verificar sintaxis: `./vendor/bin/sail php -l app/Http/Controllers/InventoryController.php`
- [x] 5.3 Acceder a la página de inventario y confirmar que no se generan consultas lazy-load para `unitOfMeasure` por cada fila

## 6. Configuración de caché Redis

- [x] 6.1 Cambiar `CACHE_STORE=database` a `CACHE_STORE=redis` en `.env`
- [x] 6.2 Verificar que el driver `redis` existe en `config/cache.php` (debe existir por defecto en Laravel con Redis configurado)
- [x] 6.3 Verificar conexión a Redis: `./vendor/bin/sail artisan tinker --execute="Cache::store('redis')->put('test', 'ok', 10); echo Cache::store('redis')->get('test');"`

## 7. Implementación de caché en DashboardController

- [x] 7.1 Agregar `use Illuminate\Support\Facades\Cache;` en `DashboardController`
- [x] 7.2 Envolver las queries de `todaySales`, `inventoryAlerts` y `activeRegisters` en `Cache::remember('dashboard:stats', 60, function () use (...) { ... })`
- [x] 7.3 Verificar sintaxis: `./vendor/bin/sail php -l app/Http/Controllers/DashboardController.php`
- [x] 7.4 Cargar el dashboard, verificar que los datos se muestran correctamente, recargar dentro de 60s y confirmar que no se ejecutan queries SQL (usando Debugbar o Telescope)

## 8. Pruebas funcionales

- [x] 8.1 Probar dashboard: cargar la página, verificar que el gráfico de 7 días muestra datos correctos y que incluye días con valor 0 si aplica
- [x] 8.2 Probar venta con producto simple: verificar que el stock se reduce correctamente y no se generan múltiples queries por item
- [x] 8.3 Probar venta con producto compuesto: verificar que los ingredientes se descuentan recursivamente con una sola consulta batch
- [x] 8.4 Probar cierre de caja: verificar que el job de notificación se despacha correctamente y que el flujo no se rompe
- [x] 8.5 Probar carga de inventario: verificar que la página carga sin consultas N+1 para unitOfMeasure
- [x] 8.6 Verificar que la migración de índices se puede revertir: `./vendor/bin/sail artisan migrate:rollback --step=1` y confirmar que los índices se eliminan sin errores
- [x] 8.7 Ejecutar tests existentes para verificar que no hay regresiones: `./vendor/bin/sail artisan test`
