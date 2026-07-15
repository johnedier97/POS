## ADDED Requirements

### Requirement: Prueba de stock negativo sin excepciones
El sistema SHALL verificar que el inventario puede tener stock negativo sin lanzar excepciones, y que las alertas del dashboard detectan stock bajo correctamente.

#### Scenario: Venta que reduce stock por debajo de cero
- **WHEN** un producto tiene stock 2 y se venden 5 unidades
- **THEN** el inventario queda en `-3` y la venta se procesa exitosamente

#### Scenario: Alerta de inventario detecta stock bajo
- **WHEN** el stock de un producto baja de 5 unidades
- **THEN** el contador `inventoryAlerts` del dashboard incluye ese producto

### Requirement: Prueba de productos compuestos con anidación profunda
El sistema SHALL verificar que `collectProductIds` resuelve correctamente productos compuestos con múltiples niveles de anidación.

#### Scenario: Producto compuesto de 3 niveles
- **WHEN** un producto A (compuesto) contiene B (compuesto), y B contiene C (simple)
- **THEN** vender 2 unidades de A descuenta el inventario de C en la cantidad correcta (2 × cantidad_B × cantidad_C)

### Requirement: Prueba de precisión decimal en cálculos
El sistema SHALL verificar que los cálculos monetarios manejan correctamente valores con decimales sin errores de redondeo.

#### Scenario: Cierre de caja con decimales coincide exactamente
- **WHEN** el balance calculado es `100.33` y el reportado es `100.33`
- **THEN** el cierre es exitoso (no hay falsos descuadres por precisión float)

#### Scenario: Descuadre mínimo se detecta correctamente
- **WHEN** el balance calculado es `100.33` y el reportado es `100.34`
- **THEN** el sistema rechaza el cierre por descuadre

### Requirement: Prueba de venta tipo waste (merma)
El sistema SHALL verificar que las ventas tipo `waste` no registran pagos y afectan el inventario sin sumar al balance de caja.

#### Scenario: Merma descuenta inventario sin registrar pago
- **WHEN** se procesa una venta con `type = 'waste'` de 3 unidades
- **THEN** el inventario se reduce en 3 y no se crean registros en la tabla `payments`

### Requirement: Prueba de integridad UNIQUE en inventories
El sistema SHALL validar que la constraint UNIQUE en `(product_id, branch_id)` previene duplicados.

#### Scenario: Insertar inventario duplicado lanza excepción
- **WHEN** se intenta insertar un segundo registro con el mismo `product_id` y `branch_id`
- **THEN** la base de datos lanza una excepción de violación de constraint única
