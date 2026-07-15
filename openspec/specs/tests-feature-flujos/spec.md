# tests-feature-flujos

## Purpose

Tests de feature que validan los flujos principales del sistema incluyendo ventas simples y con productos compuestos, cierre de caja con cuadre/descuadre, órdenes de compra (creación, recepción, reversión), y carga del dashboard con caché.

## Requirements

### Requirement: Prueba del flujo de venta simple en POS
El sistema SHALL tener un test de feature que valide el endpoint `POST /pos` para una venta con productos simples, verificando que la venta, sus detalles, pagos e inventario se procesan correctamente.

#### Scenario: Venta exitosa con producto simple y pago en efectivo
- **WHEN** un usuario con sesión abierta envía `POST /pos` con un producto simple, cantidad 2, y un pago en efectivo por el total
- **THEN** se crea un `Sale` con `type = 'sale'`, se generan `SaleDetail` y `Payment`, y el inventario del producto se reduce en 2 unidades

#### Scenario: Venta rechazada sin sesión abierta
- **WHEN** un usuario sin sesión de caja abierta envía `POST /pos`
- **THEN** el sistema responde con HTTP 403 y un mensaje de error

### Requirement: Prueba del flujo de venta con producto compuesto
El sistema SHALL tener un test que valide que los productos compuestos descuentan inventario de sus ingredientes recursivamente.

#### Scenario: Venta de producto compuesto descuenta ingredientes
- **WHEN** se vende 1 unidad de un producto compuesto que tiene 2 ingredientes (3 unidades de A, 1 unidad de B)
- **THEN** el inventario del ingrediente A se reduce en 3 y el de B en 1

### Requirement: Prueba del flujo de cierre de caja
El sistema SHALL tener tests que validen el endpoint `PUT /shift/close/{session}` incluyendo cuadre exitoso, descuadre, y despacho del Job de notificación.

#### Scenario: Cierre exitoso con cuadre exacto
- **WHEN** el balance reportado coincide con el balance calculado (initial + ventas en efectivo)
- **THEN** la sesión se marca como `closed`, se actualizan `final_calculated_balance` y `final_reported_balance`, y se despacha `SendCashRegisterClosedMail`

#### Scenario: Cierre fallido por descuadre
- **WHEN** el balance reportado no coincide con el calculado (diferencia > 0.01)
- **THEN** el sistema redirige con mensaje de error y la sesión permanece `open`

### Requirement: Prueba del flujo de órdenes de compra
El sistema SHALL tener tests para crear, recibir y revertir órdenes de compra.

#### Scenario: Recepción de orden actualiza inventario
- **WHEN** se recibe una orden de compra con 10 unidades de un producto
- **THEN** el inventario del producto en la sucursal aumenta en 10 y la orden cambia a `status = 'received'`

#### Scenario: Reversión de orden reduce inventario
- **WHEN** se revierte una orden previamente recibida
- **THEN** el inventario disminuye en la cantidad correspondiente y la orden vuelve a `status = 'pending'`

### Requirement: Prueba del dashboard
El sistema SHALL validar que el dashboard carga correctamente con caché y que el gráfico de 7 días se genera con una sola consulta.

#### Scenario: Dashboard carga con datos cacheados
- **WHEN** un usuario autenticado accede a `/dashboard` por segunda vez en 60 segundos
- **THEN** los datos se retornan desde caché sin ejecutar queries SQL adicionales

#### Scenario: Gráfico incluye días sin ventas
- **WHEN** uno de los últimos 7 días no tiene ventas registradas
- **THEN** el gráfico muestra `0` para ese día sin errores
