## ADDED Requirements

### Requirement: Índices en tabla sales para consultas por tipo y fecha
El sistema SHALL contar con índices compuestos en la tabla `sales` que aceleren las consultas que filtran por `type` y `created_at`, utilizadas en el dashboard y en los reportes de ventas.

#### Scenario: Dashboard consulta ventas agrupadas por día usando el índice
- **WHEN** el `DashboardController` ejecuta una consulta `GROUP BY DATE(created_at)` filtrando por `type = 'sale'` en un rango de 7 días
- **THEN** MySQL utiliza el índice compuesto `(type, created_at)` en lugar de un full table scan

#### Scenario: Consulta de ventas del día usa el índice
- **WHEN** el sistema consulta `Sale::where('type', 'sale')->whereDate('created_at', Carbon::today())`
- **THEN** MySQL resuelve la consulta usando el índice sin escanear toda la tabla

### Requirement: Índice en cash_register_sessions para sesiones activas
El sistema SHALL contar con un índice en la columna `status` de la tabla `cash_register_sessions` para acelerar las consultas de sesiones abiertas.

#### Scenario: Dashboard consulta sesiones activas usando el índice
- **WHEN** el `DashboardController` o `PosController` consultan `CashRegisterSession::where('status', 'open')`
- **THEN** MySQL utiliza el índice `status` en lugar de un full table scan

### Requirement: Restricción única en inventories para producto y sucursal
El sistema SHALL contar con una restricción `UNIQUE` en las columnas `(product_id, branch_id)` de la tabla `inventories` para garantizar integridad de datos y acelerar las operaciones `firstOrCreate`.

#### Scenario: firstOrCreate usa el índice único
- **WHEN** el sistema ejecuta `Inventory::firstOrCreate(['product_id' => X, 'branch_id' => Y], [...])`
- **THEN** MySQL utiliza el índice único para resolver la búsqueda en tiempo constante sin full table scan

#### Scenario: Inserción duplicada es rechazada
- **WHEN** se intenta insertar un registro con una combinación `(product_id, branch_id)` ya existente
- **THEN** la base de datos rechaza la inserción con una violación de constraint única, protegiendo la integridad de los datos
