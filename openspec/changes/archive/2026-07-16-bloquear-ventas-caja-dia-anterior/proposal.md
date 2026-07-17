## Why

Actualmente un cajero puede mantener una sesión de caja abierta indefinidamente a través de varios días sin realizar el arqueo de cierre. Esto permite acumular transacciones de múltiples días en una misma sesión, dificultando la conciliación diaria y aumentando el riesgo de discrepancias no detectadas a tiempo.

## What Changes

- Validación en `PosController::index()`: si la sesión abierta fue iniciada en un día anterior al actual, redirigir al dashboard con un mensaje obligando a cerrar la caja
- Validación en `PosController::store()`: rechazar nuevas ventas (403 JSON) si la sesión pertenece a un día anterior
- Mensaje de error visible en el dashboard cuando el cajero intenta entrar al POS con una sesión vencida
- No requiere migraciones de base de datos (usa el campo `opened_at` existente)

## Capabilities

### New Capabilities
- `validacion-cierre-diario-caja`: Bloquear ventas y acceso al POS cuando la sesión de caja fue abierta en un día anterior al actual, forzando el arqueo diario

## Impact

- **PosController**: `index()` y `store()` — agregar validación de fecha de apertura
- **Vista dashboard**: ya muestra mensajes flash (`session('error')`), sin cambios necesarios
- **Sin cambios en**: base de datos, migraciones, modelos, vistas del POS, JavaScript
