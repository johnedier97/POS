# tests-unitarios-modelos

## Purpose

Tests unitarios para validar relaciones Eloquent, atributos fillable/casts, métodos de negocio y eager loading del modelo User en cada modelo del sistema, asegurando la integridad de la capa de datos.

## Requirements

### Requirement: Pruebas de relaciones Eloquent en modelos
El sistema SHALL contar con tests unitarios que validen que cada relación definida en los modelos (`belongsTo`, `hasMany`, `hasOne`) retorna la instancia correcta del modelo relacionado.

#### Scenario: Sale pertenece a CashRegisterSession
- **WHEN** se crea una `Sale` asociada a una `CashRegisterSession`
- **THEN** `$sale->session` retorna la instancia correcta de `CashRegisterSession`

#### Scenario: Product tiene muchos ProductComponent
- **WHEN** un producto compuesto tiene 3 componentes
- **THEN** `$product->components` retorna una colección con 3 instancias de `ProductComponent`

### Requirement: Pruebas de atributos fillable y casts
El sistema SHALL contar con tests que validen que los atributos `$fillable` y `$casts` de cada modelo funcionan correctamente.

#### Scenario: CashRegisterSession hace cast de decimales
- **WHEN** se crea una sesión con `initial_balance = 100.50`
- **THEN** `$session->initial_balance` es de tipo float/double con valor `100.50`

#### Scenario: Sale solo acepta tipos válidos
- **WHEN** se intenta crear una venta con `type = 'invalid'`
- **THEN** el sistema lanza una excepción de validación de base de datos

### Requirement: Pruebas de métodos de negocio en modelos
El sistema SHALL contar con tests unitarios para los métodos de lógica de negocio definidos en los modelos.

#### Scenario: CashRegisterSession::isOpen retorna true para sesiones abiertas
- **WHEN** una sesión tiene `status = 'open'`
- **THEN** `$session->isOpen()` retorna `true`

#### Scenario: CashRegisterSession::isOpen retorna false para sesiones cerradas
- **WHEN** una sesión tiene `status = 'closed'`
- **THEN** `$session->isOpen()` retorna `false`

### Requirement: Pruebas de eager loading en modelo User
El sistema SHALL verificar que el modelo `User` carga automáticamente la relación `role` mediante `$with`.

#### Scenario: User siempre carga role sin consultas extra
- **WHEN** se obtiene un `User` desde la base de datos
- **THEN** la relación `role` ya está cargada sin ejecutar una consulta adicional
