# tests-regresion-optimizaciones

## Purpose

Tests de regresión que validan que las optimizaciones implementadas (caché del dashboard, consulta GROUP BY de 7 días, processInventory batch, eager loading de role y unitOfMeasure) funcionan correctamente y no introducen regresiones.

## Requirements

### Requirement: Prueba de regresión para caché del dashboard
El sistema SHALL validar que `Cache::remember('dashboard:stats', 60, ...)` funciona correctamente con el driver `array` (testing) y que los datos se refrescan al expirar.

#### Scenario: Datos cacheados se retornan sin consultas SQL en segunda carga
- **WHEN** el dashboard se carga dos veces en rápida sucesión
- **THEN** la segunda carga usa datos cacheados y no ejecuta queries a la tabla `sales`

### Requirement: Prueba de regresión para consulta GROUP BY del dashboard
El sistema SHALL validar que el dashboard obtiene correctamente las ventas de los últimos 7 días con una sola consulta agrupada.

#### Scenario: Ventas de 7 días se obtienen en una consulta
- **WHEN** se carga el dashboard con ventas registradas en 3 de los últimos 7 días
- **THEN** los 7 días aparecen en el array `$days` y los 4 días sin ventas tienen valor `0`

### Requirement: Prueba de regresión para processInventory batch
El sistema SHALL validar que el nuevo `processInventory` batch produce los mismos resultados que el antiguo método recursivo.

#### Scenario: Venta con 3 productos simples actualiza los 3 inventarios
- **WHEN** se venden 3 productos simples distintos en una misma transacción
- **THEN** los 3 inventarios se actualizan correctamente con una sola consulta batch de carga

### Requirement: Prueba de regresión para eager loading de role en User
El sistema SHALL validar que `Auth::user()->role` no genera consultas adicionales gracias a `$with = ['role']`.

#### Scenario: Acceder a user()->role no dispara consulta lazy
- **WHEN** se obtiene el usuario autenticado y se accede a `$user->role`
- **THEN** la relación ya está cargada (no se ejecuta una consulta adicional a la tabla `roles`)

### Requirement: Prueba de regresión para eager loading de unitOfMeasure
El sistema SHALL validar que la página de inventario no genera consultas N+1 para cargar `unitOfMeasure`.

#### Scenario: Listado de inventario con 20 productos no dispara consultas extra
- **WHEN** se carga la página de inventario con 20 registros de productos diferentes
- **THEN** no se ejecutan consultas adicionales para cargar `unitOfMeasure` de cada producto
