# Migración de Mock Vehicles a Base de Datos

Este documento explica cómo migrar los vehículos de prueba del archivo `mock-vehicles.json` a la base de datos real.

## Cambios Realizados

### 1. Actualización de la API de Vehículos

Se han actualizado los siguientes archivos para usar datos reales de la base de datos en lugar del archivo `mock-vehicles.json`:

- **`public/js/dashboard.js`**: Cambiado `API_URL` de `/mock-vehicles.json` a `/client/api/vehicles`
- **`app/views/manager/ManagerDashboard.php`**: Ahora carga vehículos desde la base de datos usando el modelo `Vehicle`

### 2. Script de Migración

Se ha creado el archivo `config/migrate_mock_vehicles.sql` que contiene:
- 30 vehículos de prueba basados en los datos de `mock-vehicles.json`
- Configurados para el tenant con ID 1
- Ubicaciones alrededor de Tortosa (lat: 40.71, lng: 0.58)

## Instrucciones de Uso

### Opción 1: Ejecutar el script SQL directamente

Si estás usando Docker:

```bash
# Copiar el archivo al contenedor
docker cp config/migrate_mock_vehicles.sql mariadb_db:/tmp/

# Ejecutar el script
docker exec -i mariadb_db mysql -u root -p${MARIADB_ROOT_PASSWORD} ${MARIADB_DATABASE} < /tmp/migrate_mock_vehicles.sql
```

### Opción 2: Usar cliente MySQL/MariaDB

```bash
mysql -h localhost -u admin -p ecomotiondb < config/migrate_mock_vehicles.sql
```

### Opción 3: Desde phpMyAdmin o similar

1. Conectar a la base de datos
2. Abrir el archivo `config/migrate_mock_vehicles.sql`
3. Copiar y ejecutar el contenido

## Verificación

Después de ejecutar la migración, verifica que los vehículos se cargaron correctamente:

```sql
SELECT COUNT(*) as total_vehicles FROM vehicles WHERE tenant_id = 1;
```

Deberías ver 30 vehículos (o más si ya existían otros).

## Probar la Aplicación

1. Asegúrate de que los contenedores Docker están corriendo:
   ```bash
   docker-compose up -d
   ```

2. Accede a la aplicación en `http://localhost:8081`

3. Inicia sesión como cliente o manager

4. El mapa y la lista de vehículos ahora mostrarán los datos reales de la base de datos

## Notas Importantes

- El script asume que existe un tenant con ID 1
- Si necesitas usar un tenant diferente, modifica el valor de `tenant_id` en el script SQL
- Los VIN (Vehicle Identification Number) son de prueba y comienzan con "TESTVIN"
- Las coordenadas están en formato POINT(lng, lat) según el estándar MySQL spatial

## Archivo Mock Original

El archivo `public/mock-vehicles.json` se puede mantener como referencia o eliminarse una vez confirmado que la migración funciona correctamente.
