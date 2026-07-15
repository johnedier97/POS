## ADDED Requirements

### Requirement: Caché de estadísticas del dashboard con Redis
El sistema SHALL almacenar en caché Redis las estadísticas del dashboard (`todaySales`, `inventoryAlerts`, `activeRegisters`) con un TTL de 60 segundos, utilizando `Cache::remember()`.

#### Scenario: Primera carga del dashboard consulta la base de datos
- **WHEN** un usuario accede al dashboard y no existe entrada en caché para las estadísticas
- **THEN** el sistema ejecuta las consultas SQL normalmente y almacena los resultados en Redis con TTL de 60 segundos

#### Scenario: Segunda carga dentro del TTL usa caché
- **WHEN** otro usuario (o el mismo) accede al dashboard dentro de los 60 segundos siguientes
- **THEN** el sistema retorna los datos desde Redis sin ejecutar consultas SQL

#### Scenario: Carga después del TTL refresca los datos
- **WHEN** un usuario accede al dashboard después de que hayan transcurrido más de 60 segundos desde la última carga
- **THEN** el sistema consulta la base de datos nuevamente y actualiza la entrada en Redis

### Requirement: Configuración de CACHE_STORE en Redis
El sistema SHALL utilizar Redis como driver de caché configurando `CACHE_STORE=redis` en el archivo `.env`.

#### Scenario: Cache::remember utiliza Redis
- **WHEN** el sistema ejecuta `Cache::remember('key', 60, fn() => ...)`
- **THEN** los datos se almacenan y recuperan desde Redis, no desde la base de datos MySQL

#### Scenario: Redis no disponible no bloquea la aplicación
- **WHEN** el contenedor Redis no está disponible o no responde
- **THEN** el sistema lanza una excepción clara de conexión a Redis, permitiendo al desarrollador identificar el problema sin bloquear otras funcionalidades del sistema
