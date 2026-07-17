## 1. Migración de base de datos

- [x] 1.1 Crear migración para agregar `consumo` al enum de la columna `type` en `sales` (`./vendor/bin/sail artisan make:migration add_consumo_to_sales_type_enum`)
- [x] 1.2 Implementar `up()` con `DB::statement("ALTER TABLE sales MODIFY COLUMN type ENUM('sale', 'waste', 'consumo') NOT NULL DEFAULT 'sale'")`
- [x] 1.3 Implementar `down()` para revertir el enum a `ENUM('sale', 'waste')`
- [x] 1.4 Ejecutar migración y verificar: `./vendor/bin/sail artisan migrate`

## 2. PosController (backend)

- [x] 2.1 Actualizar validación de `sale.type` en `store()`: cambiar `in:sale,waste` a `in:sale,waste,consumo`
- [x] 2.2 Verificar que la lógica de pagos (`$request->input('sale.type') === 'sale'`) excluye automáticamente `consumo` — sin cambios necesarios
- [x] 2.3 Verificar que `processInventory()` se ejecuta para `consumo` — sin cambios necesarios (aplica a todos los tipos)

## 3. Vista POS (index.blade.php)

- [x] 3.1 Cambiar toggle binario a ciclo de 3 estados: `sale → waste → consumo → sale` en el botón de tipo de venta (línea 138)
- [x] 3.2 Actualizar `x-text` del botón para mostrar "Venta" / "Novedad" / "Consumo"
- [x] 3.3 Agregar estilos condicionales amber para tipo `consumo` en el botón toggle
- [x] 3.4 Actualizar texto del botón de checkout para `consumo`: "Registrar Consumo" (línea 206)
- [x] 3.5 Agregar clase condicional amber al botón de checkout para `consumo`
- [x] 3.6 Asegurar que el botón de checkout se habilita para `consumo` sin requerir pagos (ya funciona para `waste`, extender a `consumo`)
- [x] 3.7 Actualizar mensaje de éxito en modal para `consumo`: "¡Consumo Interno Registrado!" con estilo amber
- [x] 3.8 Agregar mensaje contextual en modal de éxito para `consumo`

## 4. Vistas de historial y tirilla

- [x] 4.1 En `sales/index.blade.php`: agregar opción "Consumo Interno" en el filtro dropdown de tipo
- [x] 4.2 En `sales/index.blade.php`: agregar badge/etiqueta "CONSUMO" con color amber en cada fila de venta tipo `consumo`
- [x] 4.3 En `sales/index.blade.php`: agregar ícono distintivo para filas tipo `consumo`
- [x] 4.4 En `sales/show.blade.php`: agregar mensaje contextual para ventas tipo `consumo` (sin pagos registrados)
- [x] 4.5 En `sales/receipt.blade.php`: agregar badge "Consumo Interno" con estilo amber en el encabezado de la tirilla
- [x] 4.6 En `sales/receipt.blade.php`: ocultar sección de pagos para tipo `consumo`

## 5. Pruebas

- [x] 5.1 Agregar test en `PosControllerTest` que verifique creación de venta tipo `consumo` descuenta inventario
- [x] 5.2 Agregar test que verifique que ventas tipo `consumo` no registran pagos
- [x] 5.3 Agregar test que verifique que el arqueo excluye ventas tipo `consumo`
- [x] 5.4 Ejecutar todos los tests: `./vendor/bin/sail artisan test`
