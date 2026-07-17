## 1. PosController

- [x] 1.1 Agregar método privado `isSessionFromPreviousDay(CashRegisterSession $session): bool` que compare `opened_at` (fallback a `created_at`) con hoy
- [x] 1.2 En `index()`: después de obtener la sesión, agregar validación que redirija al dashboard con mensaje flash si la sesión es de día anterior
- [x] 1.3 En `store()`: después de obtener la sesión, agregar validación que retorne 403 JSON si la sesión es de día anterior

## 2. Pruebas

- [x] 2.1 Agregar test que verifique que `store()` rechaza venta con sesión del día anterior (403)
- [x] 2.2 Agregar test que verifique que `store()` permite venta con sesión del mismo día (200)
- [x] 2.3 Agregar test que verifique que `index()` redirige al dashboard con sesión del día anterior
- [x] 2.4 Agregar test que verifique que `index()` carga el POS normalmente con sesión del mismo día
- [x] 2.5 Agregar test que verifique el fallback a `created_at` cuando `opened_at` es null
- [x] 2.6 Ejecutar todos los tests: `./vendor/bin/sail artisan test`
