## ADDED Requirements

### Requirement: Prueba del middleware CheckRole
El sistema SHALL validar que el middleware `CheckRole` permite acceso a usuarios con el rol requerido y bloquea a usuarios sin el rol o no autenticados.

#### Scenario: Usuario admin accede a ruta protegida
- **WHEN** un usuario con rol `admin` accede a una ruta con middleware `role:admin`
- **THEN** la solicitud continúa normalmente (HTTP 200)

#### Scenario: Usuario sin rol es bloqueado
- **WHEN** un usuario con rol `sales` accede a una ruta con middleware `role:admin`
- **THEN** el sistema responde con HTTP 403

#### Scenario: Usuario no autenticado es redirigido
- **WHEN** un usuario no autenticado accede a una ruta protegida con `role:admin`
- **THEN** el sistema redirige a la página de login (HTTP 302)

### Requirement: Prueba de protección de rutas admin
El sistema SHALL validar que las rutas definidas en el grupo `middleware(['role:admin'])` no son accesibles por usuarios sin el rol adecuado.

#### Scenario: Cajero no puede acceder a gestión de productos
- **WHEN** un usuario con rol `sales` intenta acceder a `/products`
- **THEN** el sistema responde con HTTP 403

#### Scenario: Cajero no puede acceder a gestión de usuarios
- **WHEN** un usuario con rol `sales` intenta acceder a `/users`
- **THEN** el sistema responde con HTTP 403
