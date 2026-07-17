## ADDED Requirements

### Requirement: Registro de venta tipo consumo interno
El sistema SHALL permitir registrar transacciones de tipo `consumo` que descuentan inventario, no requieren pagos y no afectan el arqueo de caja ni los KPIs del dashboard.

#### Scenario: Cajero registra un consumo interno desde el POS
- **WHEN** un cajero selecciona el tipo "Consumo" en el toggle del POS y confirma la transacción con productos en el carrito
- **THEN** el sistema crea un registro en `sales` con `type = 'consumo'`, descuenta el inventario correspondiente, y no registra pagos

#### Scenario: El consumo interno descuenta inventario
- **WHEN** se registra una venta tipo `consumo` con productos que tienen stock
- **THEN** el sistema reduce el stock en la tabla `inventories` para la sucursal actual en la cantidad correspondiente

#### Scenario: El consumo interno no afecta el arqueo de caja
- **WHEN** se cierra una sesión de caja que contiene ventas tipo `consumo`
- **THEN** el cálculo del arqueo (`totalCashSales`) excluye las ventas con `type = 'consumo'`

#### Scenario: El consumo interno no afecta los KPIs del dashboard
- **WHEN** se calculan las métricas del dashboard (ventas del día, gráfico de 7 días)
- **THEN** las ventas con `type = 'consumo'` no se incluyen en los totales monetarios

### Requirement: Toggle de tipo de venta en el POS
El sistema SHALL ofrecer un toggle de 3 opciones en la interfaz POS para alternar entre Venta (sale), Novedad (waste) y Consumo (consumo).

#### Scenario: Cajero alterna entre los tres tipos de venta
- **WHEN** un cajero hace clic en el botón de tipo de venta
- **THEN** el tipo cambia cíclicamente: Venta → Novedad → Consumo → Venta, mostrando el texto y color correspondiente a cada tipo

#### Scenario: Botón de checkout se adapta al tipo Consumo
- **WHEN** el tipo seleccionado es "Consumo"
- **THEN** el botón de checkout se muestra en color amber, habilita el envío sin requerir pagos, y muestra el texto "Registrar Consumo"

### Requirement: Visualización del tipo consumo en historial y tirilla
El sistema SHALL mostrar el tipo `consumo` con un identificador visual distintivo en el listado de ventas y en la tirilla de comprobante.

#### Scenario: Venta tipo consumo aparece en el historial con etiqueta
- **WHEN** un usuario consulta el historial de ventas
- **THEN** las ventas con `type = 'consumo'` muestran una etiqueta/badge con el texto "CONSUMO" en color amber

#### Scenario: Tirilla de consumo muestra encabezado apropiado
- **WHEN** se genera la tirilla de una venta tipo `consumo`
- **THEN** el encabezado muestra "Comprobante de Consumo Interno" en lugar de "Comprobante de Venta"

### Requirement: Migración de base de datos para nuevo tipo
El sistema SHALL modificar la columna `type` de la tabla `sales` para aceptar el valor `consumo` mediante una migración de Laravel.

#### Scenario: La migración agrega el valor consumo al enum
- **WHEN** se ejecuta `php artisan migrate`
- **THEN** la columna `type` en `sales` acepta los valores `'sale'`, `'waste'` y `'consumo'`, con `'sale'` como valor por defecto

#### Scenario: Rollback de la migración revierte el cambio
- **WHEN** se ejecuta `php artisan migrate:rollback` en el batch de esta migración
- **THEN** la columna `type` vuelve a aceptar solo `'sale'` y `'waste'`
