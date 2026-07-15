## Why

Actualmente el envío de correos de notificación de cierre de caja se realiza directamente desde el controlador `CashRegisterSessionController` mediante `Mail::to()->queue()`, lo que acopla la lógica del controlador con la responsabilidad de despacho de correos. Se requiere refactorizar este proceso utilizando un Job dedicado de Laravel que sea procesado mediante el programador de tareas (cron/Scheduler), logrando una separación clara de responsabilidades y un flujo más mantenible y testeable. Además, se debe garantizar que el correo informe correctamente el valor del cuadre de caja (balance reportado vs. calculado) a todos los usuarios con perfil admin.

## What Changes

- Crear una clase `SendCashRegisterClosedMail` como Laravel Job dedicado para el envío de correos de cierre de caja
- Refactorizar `CashRegisterSessionController@update` para despachar el Job en lugar de enviar correos directamente
- Asegurar que el Scheduler en `routes/console.php` tenga configurado `queue:work --stop-when-empty` para procesar los Jobs via cron cada minuto
- El Job debe encapsular toda la lógica de notificación: consultar usuarios admin, construir el Mailable con los datos del cuadre (balance inicial, ventas en efectivo, balance reportado, diferencia), y enviar a cada admin
- El Mailable `CashRegisterClosedMail` existente se mantiene sin cambios en su estructura de datos
- El correo incluirá la información del cuadre: balance calculado, balance reportado y diferencia (si la caja cuadró o presenta descuadre)
- **No se requieren migraciones de base de datos.** Los cambios son exclusivamente de lógica de aplicación
- **No se requieren cambios en la interfaz de usuario (vistas Blade/Tailwind).** El flujo de cierre de caja existente permanece igual desde la perspectiva del usuario

## Capabilities

### New Capabilities
- `cron-email-cierre-caja`: Sistema de notificación por correo electrónico a administradores cuando se cierra una caja registradora, utilizando un Job de Laravel procesado mediante el programador de tareas (cron/Scheduler), informando el valor del cuadre de caja (balance calculado, balance reportado y diferencia).

### Modified Capabilities
<!-- No se modifican capacidades existentes; esta es una funcionalidad nueva -->

## Impact

- **Controladores afectados**: `CashRegisterSessionController@update` — se reemplaza el bloque de envío directo de correos por el despacho del nuevo Job
- **Nuevos archivos**: `app/Jobs/SendCashRegisterClosedMail.php` — Job dedicado al envío de correos
- **Archivos existentes modificados**: `app/Http/Controllers/CashRegisterSessionController.php`, `routes/console.php`
- **Archivos sin cambios**: `app/Mail/CashRegisterClosedMail.php`, vistas Blade, migraciones, modelos
- **Dependencias**: Sin nuevas dependencias externas; se utilizan exclusivamente clases nativas de Laravel (Jobs, Mail, Scheduler, Queue)
