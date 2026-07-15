## 1. Factories para entidades base (sin dependencias complejas)

- [x] 1.1 Crear `RoleFactory` con estados `admin` y `sales`
- [x] 1.2 Crear `BranchFactory` con nombre y dirección aleatorios
- [x] 1.3 Crear `UnitOfMeasureFactory` (unidad, kg, litro, etc.)
- [x] 1.4 Crear `PaymentMethodFactory` con estado `active`/`inactive`
- [x] 1.5 Crear `CustomerFactory` con documento, email y teléfono
- [x] 1.6 Crear `SupplierFactory` con contacto, email y teléfono
- [x] 1.7 Crear `SettingFactory` con key/value
- [x] 1.8 Actualizar `UserFactory` para asignar `role_id` automáticamente

## 2. Factories para entidades con dependencias

- [x] 2.1 Crear `CashRegisterFactory` que cree Branch automáticamente
- [x] 2.2 Crear `CashRegisterSessionFactory` con estados `open`/`closed`
- [x] 2.3 Crear `ProductFactory` con estados `composite` y `simple`
- [x] 2.4 Crear `ProductComponentFactory` que relacione parent y child products
- [x] 2.5 Crear `InventoryFactory` que cree Product + Branch automáticamente
- [x] 2.6 Crear `SaleFactory` con estado `waste`, dependencias en cascada
- [x] 2.7 Crear `SaleDetailFactory` que cree Sale + Product automáticamente
- [x] 2.8 Crear `PaymentFactory` que cree Sale + PaymentMethod
- [x] 2.9 Crear `PurchaseOrderFactory` con estados `pending`/`received`
- [x] 2.10 Crear `PurchaseOrderDetailFactory` que cree PurchaseOrder + Product

## 3. Tests unitarios de modelos

- [x] 3.1 Crear `tests/Unit/Models/RoleTest.php`
- [x] 3.2 Crear `tests/Unit/Models/BranchTest.php`
- [x] 3.3 Crear `tests/Unit/Models/CashRegisterTest.php`
- [x] 3.4 Crear `tests/Unit/Models/CashRegisterSessionTest.php`
- [x] 3.5 Crear `tests/Unit/Models/ProductTest.php`
- [x] 3.6 Crear `tests/Unit/Models/InventoryTest.php`
- [x] 3.7 Crear `tests/Unit/Models/SaleTest.php`
- [x] 3.8 Crear `tests/Unit/Models/SaleDetailTest.php`
- [x] 3.9 Crear `tests/Unit/Models/PaymentTest.php`
- [x] 3.10 Crear `tests/Unit/Models/PurchaseOrderTest.php`
- [x] 3.11 Crear `tests/Unit/Models/SupplierTest.php`
- [x] 3.12 Crear `tests/Unit/Models/UserTest.php`
- [x] 3.13 Crear `tests/Unit/Models/ProductComponentTest.php`
- [x] 3.14 Crear `tests/Unit/Models/CustomerTest.php`
- [x] 3.15 Ejecutar tests unitarios: `./vendor/bin/sail artisan test tests/Unit/Models`

## 4. Tests de feature - Flujo de ventas (POS)

- [x] 4.1 Crear `tests/Feature/Controllers/PosControllerTest.php` con test de venta simple
- [x] 4.2 Agregar test: venta rechazada sin sesión abierta (HTTP 403)
- [x] 4.3 Agregar test: venta con producto compuesto descuenta ingredientes recursivamente
- [x] 4.4 Agregar test: venta con split de pagos (efectivo + tarjeta)
- [x] 4.5 Agregar test: venta tipo `waste` (merma) no registra pagos y descuenta inventario
- [x] 4.6 Agregar test: validación de campos requeridos (sin items, sin total)
- [x] 4.7 Ejecutar tests de POS: `./vendor/bin/sail artisan test tests/Feature/Controllers/PosControllerTest`

## 5. Tests de feature - Cierre de caja

- [x] 5.1 Crear `tests/Feature/Controllers/CashRegisterSessionControllerTest.php` con test de cierre exitoso
- [x] 5.2 Agregar test: cierre fallido por descuadre (diferencia > 0)
- [x] 5.3 Agregar test: cierre exitoso despacha Job `SendCashRegisterClosedMail`
- [x] 5.4 Agregar test: apertura de caja exitosa
- [x] 5.5 Agregar test: no se puede abrir segunda caja si ya hay una abierta
- [x] 5.6 Agregar test: no se puede cerrar una caja ya cerrada
- [x] 5.7 Ejecutar tests de cierre de caja: `./vendor/bin/sail artisan test tests/Feature/Controllers/CashRegisterSessionControllerTest`

## 6. Tests de feature - Órdenes de compra

- [x] 6.1 Crear `tests/Feature/Controllers/PurchaseOrderControllerTest.php` con test de creación
- [x] 6.2 Agregar test: recepción de orden actualiza inventario (+stock)
- [x] 6.3 Agregar test: reversión de orden recibida reduce inventario
- [x] 6.4 Agregar test: no se puede recibir una orden ya recibida
- [x] 6.5 Agregar test: no se puede revertir una orden no recibida
- [x] 6.6 Ejecutar tests de compras: `./vendor/bin/sail artisan test tests/Feature/Controllers/PurchaseOrderControllerTest`

## 7. Tests de feature - Dashboard

- [x] 7.1 Crear `tests/Feature/Controllers/DashboardControllerTest.php` con test de carga
- [x] 7.2 Agregar test: gráfico de 7 días incluye días con valor 0
- [x] 7.3 Agregar test: datos cacheados se retornan desde caché en segunda carga
- [x] 7.4 Agregar test: alertas de inventario cuentan productos con stock < 5
- [x] 7.5 Ejecutar tests de dashboard: `./vendor/bin/sail artisan test tests/Feature/Controllers/DashboardControllerTest`

## 8. Tests del Job de notificación

- [x] 8.1 Crear `tests/Feature/Jobs/SendCashRegisterClosedMailTest.php` con test de envío exitoso a admins
- [x] 8.2 Agregar test: Job no falla cuando no hay administradores
- [x] 8.3 Agregar test: fallo en envío individual no detiene el Job
- [x] 8.4 Ejecutar tests del Job: `./vendor/bin/sail artisan test tests/Feature/Jobs/SendCashRegisterClosedMailTest`

## 9. Tests de autorización y middleware

- [x] 9.1 Crear `tests/Feature/Middleware/CheckRoleTest.php` con test: usuario admin accede a ruta protegida
- [x] 9.2 Agregar test: usuario sin rol adecuado recibe HTTP 403
- [x] 9.3 Agregar test: usuario no autenticado es redirigido a login
- [x] 9.4 Agregar test: cajero no puede acceder a `/users`, `/products`, `/suppliers` (HTTP 403)
- [x] 9.5 Ejecutar tests de autorización: `./vendor/bin/sail artisan test tests/Feature/Middleware/CheckRoleTest`

## 10. Tests de edge cases

- [x] 10.1 Crear `tests/Feature/EdgeCases/InventoryEdgeCaseTest.php` con test: stock negativo
- [x] 10.2 Agregar test: producto compuesto con 3 niveles de anidación
- [x] 10.3 Agregar test: precisión decimal en cierre de caja (100.33 vs 100.34)
- [x] 10.4 Agregar test: constraint UNIQUE en inventories rechaza duplicados
- [x] 10.5 Agregar test: venta sin métodos de pago activos (fallback creation)
- [x] 10.6 Ejecutar tests de edge cases: `./vendor/bin/sail artisan test tests/Feature/EdgeCases`

## 11. Tests de regresión para optimizaciones

- [x] 11.1 Crear `tests/Feature/Regression/OptimizationRegressionTest.php` con test: dashboard GROUP BY
- [x] 11.2 Agregar test: `Cache::remember` refresca al expirar TTL
- [x] 11.3 Agregar test: `User::$with = ['role']` evita N+1
- [x] 11.4 Agregar test: inventario carga `unitOfMeasure` sin queries extra
- [x] 11.5 Agregar test: `processInventory` batch actualiza múltiples productos
- [x] 11.6 Ejecutar tests de regresión: `./vendor/bin/sail artisan test tests/Feature/Regression`

## 12. Verificación final

- [x] 12.1 Ejecutar suite completa de tests: `./vendor/bin/sail artisan test`
- [x] 12.2 Verificar cobertura de código: `./vendor/bin/sail artisan test --coverage` (si XDebug está disponible)
- [x] 12.3 Revisar que no hay tests rotos o saltados
- [x] 12.4 Ejecutar linter: `./vendor/bin/sail pint`
