## Context

El módulo POS (`PosController::index()`) actualmente carga `Product::all()` sin datos de inventario. La vista `pos/index.blade.php` itera los productos en una grilla Alpine.js sin mostrar stock. La sucursal se obtiene de `$session->cashRegister->branch_id`.

El inventario está en la tabla `inventories` con columnas `(product_id, branch_id, stock)`. Recientemente se agregó un índice UNIQUE en `(product_id, branch_id)` que acelera las búsquedas.

## Goals / Non-Goals

**Goals:**
- Mostrar un badge visual "AGOTADO" (rojo) en productos con stock <= 0
- Atenuar visualmente los productos agotados (opacidad, menor contraste)
- Bloquear la adición de productos agotados al carrito desde Alpine.js
- Cargar los datos de stock en una sola consulta adicional (sin N+1)

**Non-Goals:**
- No se modifica el flujo de venta (el backend ya permite stock negativo)
- No se agregan filtros de "solo en stock" (se puede agregar después)
- No se modifica la lógica de `processInventory`
- No se modifica la base de datos

## Decisions

### 1. Cargar stock como atributo en el array de productos

**Decisión**: En `PosController::index()`, cargar el inventario de la sucursal actual con `Inventory::where('branch_id', ...)->whereIn('product_id', ...)->pluck('stock', 'product_id')` y mapear el stock a cada producto en el array que se pasa a la vista.

**Alternativa considerada**: Usar una relación `hasOne` en Product con scope de branch.  
**Razón del rechazo**: Requiere modificar el modelo Product y el scope sería complejo (requiere el branch_id del contexto). El approach de pluck es más directo y no requiere cambios en modelos.

### 2. Indicador visual con Tailwind CSS (sin JS adicional)

**Decisión**: Usar clases Tailwind condicionales en Alpine.js (`:class`) para cambiar la apariencia de las tarjetas de producto agotado: `opacity-60 grayscale`, badge `AGOTADO` en rojo, y prevenir `addProduct()` chequeando `product.stock > 0`.

**Alternativa considerada**: Componente Blade separado para producto agotado.  
**Razón del rechazo**: La grilla actual usa un solo `template` Alpine. Duplicar templates complica el mantenimiento. Las clases condicionales son más limpias.

### 3. Validación en frontend, no en backend

**Decisión**: La validación de stock se hace en el método JS `addProduct()`, mostrando un toast/notificación. El backend sigue aceptando la venta (puede haber casos legítimos de venta con stock cero o negativo, como ventas de productos que acaban de agotarse en otra caja).

**Alternativa considerada**: Bloquear en backend con validación 422.  
**Razón del rechazo**: Demasiado restrictivo. Hay escenarios válidos donde se vende sin stock (ej. producto recién agotado, el cajero ya lo tenía en carrito). La alerta es informativa, no bloqueante a nivel API.

### 4. Toast notification en vez de alert()

**Decisión**: Mostrar una notificación visual sutil (toast) cuando se intenta agregar un producto agotado, usando Alpine.js para mostrar/ocultar un elemento toast.

**Alternativa considerada**: `alert()` nativo.  
**Razón del rechazo**: Interrumpe el flujo. Un toast es menos invasivo y más profesional.

## Risks / Trade-offs

- **[Trade-off] Carga de inventario adicional**: Una query extra en cada carga del POS. La tabla inventories tiene índice UNIQUE en `(product_id, branch_id)`, así que el `whereIn` es rápido.
- **[Riesgo] Productos sin registro en inventario**: Si un producto nunca ha tenido movimientos en la sucursal actual, no tendrá registro en `inventories`. Se debe tratar `stock = 0` como `null` → mostrar como agotado o no mostrar badge (dependiendo de si queremos ser conservadores o no). → Decisión: si no hay registro, stock = 0 (agotado).
