## 1. Crear Job de notificación

- [x] 1.1 Crear la clase `app/Jobs/SendCashRegisterClosedMail.php` usando `./vendor/bin/sail artisan make:job SendCashRegisterClosedMail`
- [x] 1.2 Implementar el constructor del Job para recibir `CashRegisterSession $session`, `$totalSales` y `$finalReportedBalance`
- [x] 1.3 Implementar el método `handle()`: consultar usuarios con rol "admin" usando Eloquent con eager loading, iterar y enviar correo a cada uno con `Mail::to($admin->email)->send(new CashRegisterClosedMail(...))`
- [x] 1.4 Envolver el envío de cada correo individual en try/catch para que un fallo en un admin no detenga el envío al resto
- [x] 1.5 Agregar `declare(strict_types=1);` y tipado estricto según PSR-12
- [x] 1.6 Verificar sintaxis del archivo: `./vendor/bin/sail php -l app/Jobs/SendCashRegisterClosedMail.php`

## 2. Refactorizar Controlador

- [x] 2.1 En `app/Http/Controllers/CashRegisterSessionController.php`, agregar `use App\Jobs\SendCashRegisterClosedMail;` al bloque de imports
- [x] 2.2 Reemplazar el bloque de envío de correos (líneas 96-107) por el despacho del Job: `SendCashRegisterClosedMail::dispatch($session, $totalCashSales, $request->final_reported_balance);`
- [x] 2.3 Mantener el try/catch alrededor del despacho del Job para capturar errores de serialización o encolado
- [x] 2.4 Eliminar los imports no utilizados tras la refactorización (`use App\Mail\CashRegisterClosedMail;`, `use App\Models\User;`, `use Illuminate\Support\Facades\Mail;`)
- [x] 2.5 Verificar sintaxis del controlador: `./vendor/bin/sail php -l app/Http/Controllers/CashRegisterSessionController.php`

## 3. Verificar configuración del Scheduler

- [x] 3.1 Verificar que `routes/console.php` contenga `Schedule::command('queue:work --stop-when-empty')->everyMinute();` (ya existe, solo confirmar)
- [x] 3.2 Verificar que el driver de colas (`QUEUE_CONNECTION` en `.env`) esté configurado como `database` para entornos productivos
- [x] 3.3 Si el driver de colas es `database`, verificar que la migración de la tabla `jobs` exista: `./vendor/bin/sail artisan queue:table` si no existe

## 4. Pruebas funcionales

- [x] 4.1 Probar cierre de caja exitoso: verificar que el Job se encola correctamente y que no hay errores en el log **(requiere .env corregido)**
- [x] 4.2 Probar cierre con descuadre: verificar que el Job NO se despacha y se muestra el mensaje de error al usuario **(requiere .env corregido)**
- [x] 4.3 Verificar la recepción de correos: confirmar que los usuarios admin reciben el correo con los datos del cuadre (balance calculado, reportado, diferencia) **(requiere .env corregido)**
- [x] 4.4 Probar con driver de cola `sync`: ejecutar `./vendor/bin/sail artisan queue:work` para procesar los Jobs manualmente y verificar logs **(requiere .env corregido)**
- [x] 4.5 Comando de verificación general: `./vendor/bin/sail artisan schedule:run` para confirmar que el scheduler ejecuta correctamente **(requiere .env corregido)**

## 5. Limpieza y documentación

- [x] 5.1 Ejecutar linter de PHP si está configurado: `./vendor/bin/sail pint`
- [x] 5.2 Verificar que no quedan referencias a código eliminado (ej. `use App\Models\User;` que ya no se usa en el controlador)
- [x] 5.3 Ejecutar pruebas existentes para asegurar que no hay regresiones: `./vendor/bin/sail artisan test` **(requiere .env corregido)**
