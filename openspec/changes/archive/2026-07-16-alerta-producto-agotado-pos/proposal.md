## Why

Actualmente el módulo POS muestra todos los productos sin indicar disponibilidad de inventario. Los cajeros pueden agregar productos agotados al carrito y descubrir el problema solo al intentar procesar la venta (cuando el stock ya es negativo). Esto genera fricción en el flujo de venta y posibles conflictos con el cliente. Agregar una alerta visual de "agotado" en la grilla de productos previene este escenario y mejora la experiencia de uso.

## What Changes

- **Controlador `PosController::index()`**: Cargar el inventario de la sucursal actual junto con los productos, incluyendo el `stock` de cada producto en el JSON que se pasa a la vista
- **Vista `pos/index.blade.php`**: Agregar un badge/overlay "AGOTADO" en las tarjetas de producto cuando `stock <= 0`, con estilos Tailwind (opacidad reducida, texto tachado, badge rojo)
- **Lógica JS Alpine**: El método `addProduct()` debe validar que el producto tiene stock > 0 antes de agregarlo al carrito, mostrando una notificación visual si está agotado
- **Optimización de consulta**: Usar `with('inventories')` filtrado por `branch_id` o una subquery para evitar N+1 al cargar el stock por producto

## Capabilities

### New Capabilities
- `alerta-stock-agotado-pos`: Visualización de disponibilidad de inventario en el módulo POS con indicador "AGOTADO" para productos sin stock, incluyendo validación en frontend que bloquea la adición de productos agotados al carrito

### Modified Capabilities
<!-- No se modifican capacidades existentes -->

## Impact

- **Controladores afectados**: `PosController::index()` — se agrega carga de inventario al método existente
- **Vistas afectadas**: `resources/views/pos/index.blade.php` — se agrega badge visual y validación JS
- **Migraciones**: No se requieren
- **Dependencias**: Ninguna nueva
- **Rutas**: Sin cambios
- **Rendimiento**: Una consulta adicional con `whereIn` para cargar inventario de todos los productos de la sucursal (1 query adicional, sin N+1)
