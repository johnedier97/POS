# cron-email-cierre-caja

## Purpose

Sistema de notificación por correo electrónico a administradores cuando se cierra una caja registradora, utilizando un Job de Laravel procesado mediante el programador de tareas (cron/Scheduler), informando el valor del cuadre de caja (balance calculado, balance reportado y diferencia).

## Requirements

### Requirement: Despachar Job al cerrar caja registradora
El sistema SHALL despachar un Job `SendCashRegisterClosedMail` inmediatamente después de cerrar una sesión de caja registradora, reemplazando el envío directo de correos desde el controlador.

#### Scenario: Cierre exitoso de caja despacha el Job
- **WHEN** un usuario cierra una sesión de caja registradora satisfactoriamente (balance reportado coincide con el calculado)
- **THEN** el sistema despacha un Job `SendCashRegisterClosedMail` con los parámetros de la sesión cerrada, total de ventas en efectivo y balance reportado

#### Scenario: Cierre fallido por descuadre no despacha Job
- **WHEN** un usuario intenta cerrar una sesión pero el balance reportado no coincide con el calculado
- **THEN** el sistema NO despacha el Job de notificación y retorna un mensaje de error al usuario

### Requirement: Job de notificación por correo a administradores
El sistema SHALL contar con un Job `SendCashRegisterClosedMail` que, al ejecutarse, envíe un correo electrónico a todos los usuarios con rol "admin" informando los detalles del cuadre de caja (balance calculado, balance reportado y diferencia).

#### Scenario: Envío exitoso de correos a todos los administradores
- **WHEN** el Job `SendCashRegisterClosedMail` se ejecuta con los datos de una sesión cerrada
- **THEN** el sistema envía un correo a cada usuario con rol "admin" utilizando el Mailable `CashRegisterClosedMail`, incluyendo el balance inicial, total de ventas en efectivo, balance reportado y diferencia de cuadre

#### Scenario: No hay administradores registrados
- **WHEN** el Job se ejecuta pero no existen usuarios con rol "admin" en el sistema
- **THEN** el Job finaliza correctamente sin enviar correos y sin lanzar excepciones

#### Scenario: Fallo en el envío de un correo individual
- **WHEN** el Job intenta enviar un correo a un administrador y el servicio SMTP falla
- **THEN** el Job registra el error en el log del sistema y continúa intentando enviar a los administradores restantes

### Requirement: Procesamiento de Jobs mediante el Scheduler
El sistema SHALL procesar los Jobs encolados a través del programador de tareas de Laravel (Scheduler), ejecutando `queue:work --stop-when-empty` cada minuto.

#### Scenario: El Scheduler procesa Jobs pendientes
- **WHEN** existen Jobs `SendCashRegisterClosedMail` pendientes en la cola
- **THEN** el Scheduler ejecuta el worker de colas en el siguiente ciclo (máximo 60 segundos después) y procesa todos los Jobs pendientes antes de detenerse

#### Scenario: No hay Jobs pendientes
- **WHEN** el Scheduler ejecuta `queue:work --stop-when-empty` y no hay Jobs en la cola
- **THEN** el worker finaliza inmediatamente sin consumir recursos adicionales
