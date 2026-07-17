## Why

El módulo POS actual solo soporta dos tipos de transacción: `sale` (venta normal, afecta arqueo) y `waste` (novedad/merma, no afecta arqueo). No existe un tipo que permita registrar consumo interno (ej. productos consumidos por empleados, degustaciones, cortesías) como una venta formal que descuente inventario pero que no impacte el arqueo de caja ni los KPIs de ingresos del dashboard. Actualmente estos casos se registran como `waste`, lo cual es semánticamente incorrecto (no son pérdidas) y distorsiona los reportes de mermas.

## What Changes

- Nuevo tipo de venta `consumo` en la tabla `sales` (migración de enum)
- Toggle en el POS para alternar entre Venta / Novedad / Consumo
- El tipo `consumo` descuenta inventario (misma lógica que `sale` y `waste`)
- El tipo `consumo` NO requiere pagos (igual que `waste`)
- El tipo `consumo` se excluye del arqueo de caja (igual que `waste`)
- El tipo `consumo` se excluye de los KPIs monetarios del dashboard (igual que `waste`)
- Nueva etiqueta visual "Consumo" en vistas: POS, historial de ventas, tirilla

## Capabilities

### New Capabilities
- `tipo-venta-consumo-interno`: Registro de ventas de tipo "consumo interno" que descuentan inventario sin afectar arqueo de caja ni KPIs de ingresos

## Impact

- **Migración**: Alterar columna `type` en tabla `sales` para agregar valor `consumo` al enum
- **PosController**: Validación (`in:sale,waste,consumo`), lógica de pago (excluir `consumo` de registro de pagos)
- **CashRegisterSessionController**: Sin cambios (el arqueo filtra por `type = 'sale'`, `consumo` queda excluido automáticamente)
- **DashboardController**: Sin cambios (KPIs filtran por `type = 'sale'`)
- **Vista POS** (`pos/index.blade.php`): Toggle de 3 opciones, estilos para tipo `consumo`
- **Vista historial** (`sales/index.blade.php`): Filtro y etiqueta para `consumo`
- **Vista detalle** (`sales/show.blade.php`): Mensaje contextual para `consumo`
- **Vista tirilla** (`sales/receipt.blade.php`): Badge para `consumo`
