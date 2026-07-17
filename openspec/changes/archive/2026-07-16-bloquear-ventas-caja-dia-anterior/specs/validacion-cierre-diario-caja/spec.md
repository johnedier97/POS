## ADDED Requirements

### Requirement: Bloqueo de ventas con sesión de caja de día anterior
El sistema SHALL impedir nuevas ventas cuando la sesión de caja abierta fue iniciada en un día anterior al actual, forzando al cajero a cerrar la caja y realizar el arqueo diario.

#### Scenario: Cajero intenta vender con sesión del día anterior
- **WHEN** un cajero con una sesión abierta cuya fecha de apertura es anterior a hoy intenta crear una venta
- **THEN** el sistema rechaza la transacción con un error 403 y el mensaje "Debes cerrar la caja y realizar el arqueo antes de continuar"

#### Scenario: Cajero intenta acceder al POS con sesión del día anterior
- **WHEN** un cajero con una sesión abierta cuya fecha de apertura es anterior a hoy intenta acceder al módulo POS
- **THEN** el sistema redirige al dashboard con un mensaje flash de error indicando que debe cerrar la caja

#### Scenario: Cajero vende normalmente con sesión del mismo día
- **WHEN** un cajero con una sesión abierta hoy crea una venta
- **THEN** la venta se procesa normalmente sin bloqueos

#### Scenario: Cajero accede al POS con sesión del mismo día
- **WHEN** un cajero con una sesión abierta hoy accede al módulo POS
- **THEN** el sistema carga el POS normalmente

### Requirement: Manejo de sesión sin fecha de apertura explícita
El sistema SHALL usar `created_at` como fallback cuando `opened_at` sea null para determinar la fecha de la sesión.

#### Scenario: Sesión con opened_at null usa created_at
- **WHEN** una sesión abierta tiene `opened_at = null` pero `created_at` es de un día anterior
- **THEN** el sistema bloquea las ventas usando `created_at` como referencia de fecha
