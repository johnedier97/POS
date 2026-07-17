## Context

`PosController::index()` y `store()` obtienen la sesión abierta con `Auth::user()->sessions()->where('status', 'open')->latest()->first()`. Solo verifican que exista una sesión con `status = 'open'`, sin validar su antigüedad. El campo `opened_at` (nullable datetime, con cast a Carbon) registra cuándo se abrió la sesión pero nunca se usa como validación.

## Goals / Non-Goals

**Goals:**
- Bloquear el acceso al módulo POS (`index()`) si la sesión abierta es de un día anterior
- Bloquear la creación de ventas (`store()`) si la sesión abierta es de un día anterior
- Mostrar mensaje claro al cajero indicando que debe cerrar la caja y hacer el arqueo

**Non-Goals:**
- No se modifica el flujo de apertura de caja
- No se cierra automáticamente la sesión (el cajero debe hacerlo manualmente)
- No se modifica la base de datos ni las migraciones
- No se modifica el dashboard ni las vistas del POS

## Decisions

### 1. Comparar `opened_at` con `startOfDay()`, fallback a `created_at`

**Decisión**: Usar `$session->opened_at ?? $session->created_at` como fecha de referencia, comparando `->startOfDay()` con `now()->startOfDay()`. Si la fecha de apertura es estrictamente menor a hoy, bloquear.

**Alternativa considerada**: Usar solo `created_at`.  
**Razón del rechazo**: `opened_at` es semánticamente más correcto (representa la apertura de la sesión). `created_at` es el timestamp del registro en BD. El fallback cubre casos donde `opened_at` sea null.

### 2. Método helper `isSessionFromPreviousDay()` en el controlador

**Decisión**: Extraer la lógica de validación a un método privado en `PosController` para evitar duplicación entre `index()` y `store()`.

```php
private function isSessionFromPreviousDay(CashRegisterSession $session): bool
{
    $openedDate = ($session->opened_at ?? $session->created_at)->startOfDay();
    return $openedDate->lt(now()->startOfDay());
}
```

### 3. Bloquear en ambos endpoints: index y store

**Decisión**: Validar en `index()` (redirigir con mensaje flash) y en `store()` (responder 403 JSON). Esto da doble protección: no se puede acceder al POS ni crear ventas por API.

**Alternativa considerada**: Solo validar en `store()`.  
**Razón del rechazo**: Si solo se valida en `store()`, el cajero puede navegar el POS, armar un carrito, y frustrarse al intentar cobrar. Es mejor bloquear el acceso desde el inicio.

## Risks / Trade-offs

- **[Riesgo] Zona horaria**: `now()` usa la zona horaria configurada en Laravel (`config/app.php`). Si el servidor y el cliente están en zonas diferentes, podría haber desfase. → Mitigación: Se compara por fecha (`startOfDay()`), no por hora exacta. Pequeñas diferencias de zona horaria no deberían afectar el corte de medianoche.
- **[Riesgo] `opened_at` null en sesiones antiguas**: Sesiones creadas antes de que se poblara `opened_at` podrían tener el campo null. → Mitigación: Fallback a `created_at`.
