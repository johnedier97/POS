## 1. Modificar PosController para cargar inventario

- [x] 1.1 En `PosController::index()`, obtener `$branchId = $session->cashRegister->branch_id`
- [x] 1.2 Cargar stock de inventario en una consulta batch
- [x] 1.3 Mapear el stock a cada producto: agregar atributo `stock` (0 por defecto)
- [x] 1.4 Verificar sintaxis: sin errores

## 2. Agregar badge visual "AGOTADO" en la vista

- [x] 2.1 Agregar badge `AGOTADO` en tarjeta de producto con `x-show`
- [x] 2.2 Badge con Tailwind: `bg-red-500 text-white` posicionado absoluto
- [x] 2.3 Atenuar productos agotados: `opacity-50 grayscale pointer-events-none`
- [x] 2.4 Agregar elemento toast para notificaciones

## 3. Agregar validación en Alpine.js

- [x] 3.1 Modificar `addProduct(product)` para validar stock <= 0
- [x] 3.2 Mostrar toast: "Producto agotado: <nombre>"
- [x] 3.3 Toast se oculta automáticamente después de 3 segundos

## 4. Pruebas

- [x] 4.1 Agregar test en `PosControllerTest` que verifique productos con stock
- [x] 4.2 Test que verifica badge "AGOTADO" en la vista
- [x] 4.3 Ejecutar tests: 111/111 OK (0 errors, 0 failures)
