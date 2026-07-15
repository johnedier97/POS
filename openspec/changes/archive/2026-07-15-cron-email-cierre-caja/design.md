## Context

Actualmente, cuando se cierra una caja registradora, el método `update()` de `CashRegisterSessionController` envía correos electrónicos directamente a los administradores usando `Mail::to($admin->email)->queue(new CashRegisterClosedMail(...))`. Esto acopla el controlador a la lógica de envío (consulta de admins + despacho de correos). La arquitectura de colas ya existe: el Scheduler en `routes/console.php` ejecuta `queue:work --stop-when-empty` cada minuto para procesar jobs encolados.

El cambio introduce un Job dedicado de Laravel (`SendCashRegisterClosedMail`) que encapsula toda la responsabilidad de notificación, desacoplando el controlador y mejorando la mantenibilidad y testeabilidad del código.

**Restricciones del proyecto:**
- PHP 8.3, Laravel 13, entorno Docker/Sail
- Cambios en BD únicamente mediante migraciones (no aplica aquí)
- Uso de Eloquent con eager loading para evitar N+1
- Estándar PSR-12 y tipado estricto

## Goals / Non-Goals

**Goals:**
- Crear una clase `SendCashRegisterClosedMail` como Laravel Job que encapsule la lógica de envío de correos de cierre de caja
- Refactorizar `CashRegisterSessionController@update` para despachar el Job en lugar de enviar correos directamente
- Garantizar que el Scheduler procese los Jobs correctamente mediante el cron existente
- El correo debe informar el balance calculado, balance reportado y diferencia (cuadre/descuadre)

**Non-Goals:**
- No se modifica la estructura del Mailable `CashRegisterClosedMail` ni la vista de correo `emails/cash_register_closed`
- No se modifica la lógica de validación y cierre de sesión en el controlador
- No se modifican migraciones, modelos, ni vistas Blade
- No se añaden dependencias externas
- No se modifica el comportamiento visible para el usuario final

## Decisions

### 1. Usar un Laravel Job en lugar de un Custom Artisan Command

**Decisión:** Crear `App\Jobs\SendCashRegisterClosedMail` como un `Job` de Laravel.

**Alternativa considerada:** Crear un comando de Artisan (`php artisan send:cash-register-mail`) y programarlo en el Scheduler.

**Razón del rechazo:** Un Artisan Command programado requeriría almacenar las sesiones pendientes de notificación en la base de datos y consultarlas periódicamente, añadiendo complejidad innecesaria. El enfoque con Job es más natural: el controlador despacha el Job inmediatamente después del cierre, y el Scheduler lo procesa en el siguiente ciclo del cron. Además, Laravel ya proporciona reintentos automáticos y manejo de fallos para Jobs.

### 2. Mantener el Mailable existente sin cambios

**Decisión:** El Job usará `CashRegisterClosedMail` tal como existe actualmente, sin modificar su estructura ni la vista asociada.

**Alternativa considerada:** Refactorizar también el Mailable para que reciba diferentes parámetros o use otra vista.

**Razón del rechazo:** El Mailable actual ya contiene toda la información requerida (sesión, ventas, balance reportado, diferencia). Modificarlo añadiría riesgo sin beneficio. El cambio se limita a cómo se dispara el envío, no a qué se envía.

### 3. Despachar un Job por sesión (no un Job por admin)

**Decisión:** El Job itera sobre todos los administradores y envía un correo a cada uno. Se despacha un único Job por cada cierre de caja.

**Alternativa considerada:** Despachar un Job separado por cada administrador.

**Razón del rechazo:** Despachar múltiples Jobs (uno por admin) añade overhead de serialización y encolado sin beneficio real. Un solo Job que itere los admins es más simple y eficiente para este volumen de correos.

### 4. Usar el Scheduler existente sin modificaciones

**Decisión:** Mantener `Schedule::command('queue:work --stop-when-empty')->everyMinute()` en `routes/console.php` sin cambios.

**Alternativa considerada:** Cambiar a un daemon de queue worker permanente o reducir la frecuencia.

**Razón del rechazo:** El enfoque actual (worker efímero cada minuto) es adecuado para notificaciones de cierre de caja donde una latencia de hasta 60 segundos es aceptable. Un daemon permanente consumiría más recursos del contenedor.

## Risks / Trade-offs

- **[Riesgo] Fallo en el envío de correos si el servicio SMTP no está disponible** → El Job se re-intentará automáticamente según la configuración de colas de Laravel. Si agota reintentos, se registrará en la tabla `failed_jobs`. Mitigación: mantener el bloque try/catch existente más el manejo de fallos nativo de Laravel Jobs.

- **[Riesgo] Latencia en la entrega del correo** → Dado que el Scheduler procesa la cola cada minuto, el correo puede tardar hasta ~60 segundos en enviarse después del cierre. Esto es aceptable para notificaciones administrativas (no es tiempo real).

- **[Trade-off] El cierre de caja y el despacho del Job no son transaccionales** → Si el Job falla definitivamente, la sesión ya está cerrada y no se reabre. Los administradores no recibirían la notificación. Mitigación: el sistema actual ya tiene este mismo comportamiento; el Job añade la capa de `failed_jobs` para trazabilidad de fallos.
