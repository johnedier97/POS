## Context

El módulo POS tiene actualmente dos tipos de transacción: `sale` (venta normal con cobro y arqueo) y `waste` (novedad/merma sin cobro ni arqueo). Ambos descuentan inventario. El toggle en la vista POS es binario (Venta ↔ Novedad). La columna `type` en la tabla `sales` es un `ENUM('sale', 'waste')` con un índice compuesto `(type, created_at)`.

Se necesita un tercer tipo `consumo` para registrar consumo interno (empleados, degustaciones) que:
- Descuente inventario
- No requiera pagos
- No afecte el arqueo de caja
- No distorsione KPIs del dashboard
- Aparezca en el historial como una venta legítima (no como merma)

## Goals / Non-Goals

**Goals:**
- Agregar `consumo` al enum de la columna `type` en `sales` vía migración
- Modificar validación en `PosController` para aceptar el nuevo tipo
- Agregar tercera opción en el toggle del POS (Venta / Novedad / Consumo)
- Estilizar el tipo `consumo` en todas las vistas (POS, historial, detalle, tirilla)
- Excluir `consumo` del arqueo de caja (ya ocurre automáticamente — el arqueo filtra `type = 'sale'`)
- Excluir `consumo` de KPIs del dashboard (ya ocurre automáticamente)

**Non-Goals:**
- No se modifica la lógica de `processInventory` (el descuento de inventario aplica igual para todos los tipos)
- No se agregan reportes específicos de consumo (puede agregarse después)
- No se modifica el modelo `CashRegisterSession` ni el job de email de cierre
- No se modifica la API de facturación electrónica

## Decisions

### 1. Migración: ALTER ENUM en vez de cambiar a VARCHAR

**Decisión**: Usar `DB::statement()` para alterar el enum existente agregando `consumo`: `ENUM('sale', 'waste', 'consumo')`.

**Alternativa considerada**: Cambiar la columna a `VARCHAR` con validación en PHP.  
**Razón del rechazo**: El enum actual tiene un índice `sales_type_created_at_index`. Cambiar a VARCHAR requiere recrear el índice sin beneficio real. Agregar un valor al enum es una operación atómica en MySQL 8.0+.

### 2. Toggle de 3 opciones: ciclo secuencial

**Decisión**: Cambiar el botón toggle actual de binario a un ciclo de 3 estados: `sale → waste → consumo → sale`. El botón muestra el nombre del tipo actual y al hacer clic avanza al siguiente. Se usan colores distintos: indigo (venta), orange (novedad), amber (consumo).

**Alternativa considerada**: Tres botones separados tipo radio.  
**Razón del rechazo**: Ocupa más espacio horizontal. El ciclo es compacto y el usuario recibe feedback inmediato con el color y texto del botón.

### 3. Sin pagos para consumo (igual que waste)

**Decisión**: El tipo `consumo` no registra pagos. La validación existente en `PosController` (`$request->input('sale.type') === 'sale'`) automáticamente excluye tanto `waste` como `consumo` del flujo de pagos.

**Alternativa considerada**: Permitir pago opcional de $0 con método "Consumo Interno".  
**Razón del rechazo**: Complejidad innecesaria. El consumo interno por definición no involucra dinero. Si en el futuro se necesita, se puede agregar como método de pago sin tocar el tipo de venta.

### 4. Color amber/amarillo para identificar Consumo

**Decisión**: Usar `amber-500` como color identificador del tipo `consumo` en toda la interfaz (botón toggle, badge en grilla, mensajes).

**Colores asignados:**
- `sale` → indigo (existente)
- `waste` → orange (existente)
- `consumo` → amber (nuevo)

## Risks / Trade-offs

- **[Riesgo] Migración de enum en producción**: Si hay registros en la tabla `sales` durante la migración, ALTER ENUM en MySQL 8.0 con nuevos valores es seguro (no afecta datos existentes). Se usa `MODIFY COLUMN` que mantiene los datos intactos.
- **[Trade-off] Sin toggle de pago opcional**: El consumo interno nunca registra pagos. Si un negocio requiere cobrar consumos internos (ej. empleado paga un porcentaje), deberá usarse el tipo `sale` normal. Esto mantiene simple la semántica.
- **[Riesgo] Rollback**: El downgrade de la migración revierte el enum a los valores originales. Si existen registros con `type = 'consumo'`, el rollback fallará. → Documentar en la migración que el downgrade requiere eliminar o reasignar esos registros primero.

## Migration Plan

1. Ejecutar migración para agregar `consumo` al enum
2. Desplegar código (controlador + vistas)
3. Sin necesidad de data migration — los registros existentes no se modifican
4. Rollback: revertir migración (requiere limpiar registros `consumo` primero)
