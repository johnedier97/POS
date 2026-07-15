## ADDED Requirements

### Requirement: Dashboard consulta ventas agrupadas en una sola query
El sistema SHALL reemplazar el loop de 7 consultas individuales en `DashboardController::index()` por una única consulta con `GROUP BY DATE(created_at)` para obtener las ventas de los últimos 7 días.

#### Scenario: Carga del dashboard con una sola consulta agrupada
- **WHEN** un usuario autenticado accede al dashboard
- **THEN** el sistema ejecuta UNA consulta con `WHERE type = 'sale' AND created_at BETWEEN ? AND ? GROUP BY DATE(created_at)` para obtener las ventas de los últimos 7 días, en lugar de 7 consultas individuales

#### Scenario: Días sin ventas retornan cero
- **WHEN** uno o más días del rango de 7 días no tienen ventas registradas
- **THEN** el sistema completa los días faltantes con valor `0` en el array de resultados desde PHP, sin consultas adicionales

### Requirement: processInventory procesa inventario con consultas batch
El sistema SHALL refactorizar el método `processInventory()` en `PosController` para recolectar todos los IDs de productos (incluyendo componentes recursivos) y realizar las consultas en batch con `whereIn` y `findMany`, eliminando el patrón N+1 recursivo.

#### Scenario: Venta de producto simple procesa inventario con una consulta
- **WHEN** se procesa una venta con N items de productos simples (no compuestos)
- **THEN** el sistema realiza UNA consulta para cargar los productos y UNA consulta batch para actualizar el inventario, en lugar de 2N consultas individuales

#### Scenario: Venta de producto compuesto procesa inventario recursivo en batch
- **WHEN** se procesa una venta con productos compuestos que contienen subcomponentes
- **THEN** el sistema recolecta recursivamente todos los IDs de productos involucrados y realiza UNA consulta `whereIn` para cargarlos todos, en lugar de una consulta por cada producto y subcomponente

### Requirement: Carga eager de role en el modelo User
El sistema SHALL cargar automáticamente la relación `role` en el modelo `User` mediante el atributo `$with` para eliminar las consultas lazy-load extra en el middleware `CheckRole` y en la vista de navegación.

#### Scenario: Cada página carga role sin consultas adicionales
- **WHEN** un usuario autenticado navega a cualquier página que incluya la barra de navegación y pase por el middleware `CheckRole`
- **THEN** la relación `role` ya está cargada en el modelo `User` sin generar consultas adicionales a la base de datos

#### Scenario: Usuario sin role asignado no genera error
- **WHEN** un usuario autenticado no tiene un `role_id` asignado (relación nula)
- **THEN** el sistema maneja gracefulmente `Auth::user()->role` como `null` sin lanzar excepciones ni errores 500

### Requirement: InventoryController carga eager de unitOfMeasure
El sistema SHALL modificar `InventoryController::index()` para incluir `product.unitOfMeasure` en la carga eager, eliminando el N+1 en la vista de inventario.

#### Scenario: Listado de inventario no genera consultas lazy-load
- **WHEN** un usuario administrador accede a la página de inventario con múltiples registros
- **THEN** el sistema carga la relación `unitOfMeasure` de cada producto en la consulta inicial, sin generar queries adicionales por cada fila de la tabla
