# tests-job-notificacion

## Purpose

Tests para el Job SendCashRegisterClosedMail que validan el envío de correos a administradores tras un cierre de caja, incluyendo escenarios sin administradores, fallos individuales de envío, y la correcta integración con el controlador de cierre.

## Requirements

### Requirement: Prueba de envío de correo a administradores
El sistema SHALL tener tests que validen el comportamiento del Job `SendCashRegisterClosedMail` en diferentes escenarios.

#### Scenario: El Job envía correo a todos los administradores
- **WHEN** se ejecuta el Job con una sesión cerrada y existen 3 usuarios con rol `admin`
- **THEN** se envían 3 correos con `CashRegisterClosedMail` incluyendo balance inicial, ventas en efectivo, balance reportado y diferencia

#### Scenario: El Job no falla cuando no hay administradores
- **WHEN** se ejecuta el Job pero no existen usuarios con rol `admin`
- **THEN** el Job finaliza correctamente sin lanzar excepciones ni enviar correos

#### Scenario: El Job continúa si un envío individual falla
- **WHEN** el Job intenta enviar correo a 3 admins y el segundo falla por error SMTP
- **THEN** el error se registra en el log y el Job continúa enviando al tercer admin

### Requirement: Prueba de despacho del Job desde el controlador
El sistema SHALL validar que el cierre de caja exitoso despacha el Job, y que el cierre fallido no lo hace.

#### Scenario: Cierre exitoso despacha el Job
- **WHEN** un cierre de caja es exitoso (cuadre exacto)
- **THEN** el Job `SendCashRegisterClosedMail` se despacha con los parámetros correctos

#### Scenario: Cierre fallido no despacha el Job
- **WHEN** un cierre de caja falla por descuadre
- **THEN** el Job NO se despacha y el flujo termina antes de llegar al bloque de notificación
