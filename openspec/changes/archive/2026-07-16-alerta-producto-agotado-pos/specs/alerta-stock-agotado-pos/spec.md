## ADDED Requirements

### Requirement: Visualización de stock agotado en grilla de productos
El sistema SHALL mostrar un indicador visual "AGOTADO" en las tarjetas de producto del módulo POS cuando el stock del producto en la sucursal actual es menor o igual a cero.

#### Scenario: Producto con stock cero muestra badge de agotado
- **WHEN** un cajero accede al módulo POS y la sucursal tiene un producto con stock = 0
- **THEN** la tarjeta del producto muestra un badge rojo con el texto "AGOTADO" y la tarjeta aparece atenuada visualmente (opacidad reducida)

#### Scenario: Producto con stock positivo no muestra badge
- **WHEN** un cajero accede al módulo POS y la sucursal tiene un producto con stock > 0
- **THEN** la tarjeta del producto se muestra normalmente sin badge de agotado

#### Scenario: Producto sin registro de inventario se considera agotado
- **WHEN** un producto existe pero no tiene registro en la tabla `inventories` para la sucursal actual
- **THEN** el sistema considera su stock como 0 y muestra el badge "AGOTADO"

### Requirement: Bloqueo de adición de productos agotados al carrito
El sistema SHALL prevenir que el cajero agregue productos con stock agotado al carrito de venta desde la interfaz Alpine.js.

#### Scenario: Click en producto agotado no lo agrega al carrito
- **WHEN** un cajero hace clic en un producto cuyo stock es <= 0
- **THEN** el producto NO se agrega al carrito y se muestra una notificación toast indicando "Producto agotado"

#### Scenario: Producto con stock disponible se agrega normalmente
- **WHEN** un cajero hace clic en un producto con stock > 0
- **THEN** el producto se agrega al carrito sin ninguna advertencia

### Requirement: Carga eficiente de inventario en PosController
El sistema SHALL cargar el stock de inventario de todos los productos de la sucursal actual en una sola consulta para evitar N+1.

#### Scenario: El controlador carga inventario con una consulta batch
- **WHEN** `PosController::index()` carga los productos para la vista POS
- **THEN** el sistema ejecuta UNA consulta `Inventory::where('branch_id', ...)->whereIn('product_id', ...)->pluck('stock', 'product_id')` para todos los productos, sin consultas individuales por producto
