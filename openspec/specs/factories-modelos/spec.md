# factories-modelos

## Purpose

Model factories para todas las entidades del dominio que permiten generar datos de prueba realistas con resolución automática de dependencias, overrides de atributos y state methods para escenarios comunes de negocio (productos compuestos, sesiones abiertas/cerradas, órdenes recibidas).

## Requirements

### Requirement: Factories para todas las entidades del dominio
El sistema SHALL contar con model factories para cada modelo Eloquent de la aplicación, permitiendo generar datos de prueba realistas y facilitando la escritura de tests.

#### Scenario: Factory genera una instancia válida del modelo
- **WHEN** se invoca `ModelName::factory()->create()`
- **THEN** se persiste un registro válido en la base de datos con todos los atributos requeridos

#### Scenario: Factory resuelve dependencias automáticamente
- **WHEN** se crea un `Sale` con `Sale::factory()->create()` sin especificar relaciones
- **THEN** la factory crea automáticamente `User`, `CashRegisterSession`, `CashRegister`, `Branch` y `Role` necesarios

#### Scenario: Factory acepta overrides de atributos
- **WHEN** se invoca `Sale::factory()->create(['total' => 99.99])`
- **THEN** el registro se crea con `total = 99.99` mientras los demás atributos usan valores generados por faker

### Requirement: State methods para escenarios comunes
El sistema SHALL incluir state methods en las factories para representar estados de negocio frecuentes, como productos compuestos, sesiones abiertas/cerradas, y órdenes recibidas.

#### Scenario: State composite en ProductFactory
- **WHEN** se invoca `Product::factory()->composite()->create()`
- **THEN** el producto se crea con `is_composite = true`

#### Scenario: State open/closed en CashRegisterSessionFactory
- **WHEN** se invoca `CashRegisterSession::factory()->open()->create()`
- **THEN** la sesión se crea con `status = 'open'` y `closed_at = null`
