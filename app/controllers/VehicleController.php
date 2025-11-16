<?php
require_once __DIR__ . '/../models/Vehicle.php';
require_once __DIR__ . '/../middleware/TenantContext.php';

class VehicleController
{
    private $vehicle;
    private $tenantId;

    public function __construct()
    {
        // Get tenant context
        $this->tenantId = method_exists('TenantContext', 'tenantId') ? (int)(TenantContext::tenantId() ?? 0) : 0;
        
        if ($this->tenantId <= 0) {
            // Fallback to session
            $this->tenantId = isset($_SESSION['tenant_id']) ? (int)$_SESSION['tenant_id'] : 0;
        }
        
        if ($this->tenantId <= 0) {
            http_response_code(400);
            die('Tenant context required');
        }
        
        // Controlador per les operacions CRUD sobre vehicles
        $this->vehicle = new Vehicle($this->tenantId);
    }

    public function index()
    {
        $vehicles = $this->vehicle->getAll();
        if ($vehicles === false) {
            $vehicles = [];
            error_log("Error getting vehicles for tenant {$this->tenantId}");
        }
        require_once __DIR__ . '/../views/vehicles/vehicles.php';
    }

    public function create()
    {
        // Mostra el formulari per crear un vehicle
        require_once __DIR__ . '/../views/vehicles/vehicle_create.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->create();
            return;
        }

        $data = [
            'vin'              => $_POST['vin'] ?? '',
            'model'            => $_POST['model'] ?? '',
            'battery_capacity' => $_POST['battery_capacity'] ?? null,
            'status'           => $_POST['status'] ?? 'available',
            'location'         => $_POST['location'] ?? null,
            'last_maintenance' => $_POST['last_maintenance'] ?? null,
            'sensor_data'      => $_POST['sensor_data'] ?? null
        ];

        // Normalitza strings buits a null per tal d'evitar inserir cadenes buides a la BD
        foreach ($data as $k => $v) {
            if ($v === '') $data[$k] = null;
        }
        
        $result = $this->vehicle->create($data);
        if ($result === false) {
            // Error en persistència: mostrem missatge d'error i re-obrim el formulari
            $_SESSION['error'] = 'Error en crear el vehicle. Revisa dades i format.';
            $this->create();
            return;
        }

        // Insert correcte: flash de success i redirecció
        $_SESSION['success'] = 'Vehicle creat correctament!';
        header('Location: /vehicles');
        exit;
    }

    public function edit()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            header('Location: /vehicles'); exit;
        }
        $vehicle = $this->vehicle->findById($id);
        // Carrega la vista d'edició amb les dades del vehicle
        require_once __DIR__ . '/../views/vehicles/vehicle_edit.php';
    }

    public function update()
    {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) { header('Location: /vehicles'); exit; }

        $data = [
            'vin' => $_POST['vin'] ?? null,
            'model' => $_POST['model'] ?? null,
            'battery_capacity' => $_POST['battery_capacity'] ?? null,
            'status' => $_POST['status'] ?? null,
            'location' => $_POST['location'] ?? null,
            'last_maintenance' => $_POST['last_maintenance'] ?? null,
            'sensor_data' => $_POST['sensor_data'] ?? null
        ];
        // Normalitza i actualitza, després posa una alerta de tipus warning
        foreach ($data as $k => $v) if ($v === '') $data[$k] = null;

        $this->vehicle->update($id, $data);
        $_SESSION['warning'] = 'Vehicle actualitzat.';
        header('Location: /vehicles'); exit;
    }

    public function delete()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $this->vehicle->delete($id);
            // Esborra i mostra flash de perill (danger) per indicar eliminació
            $_SESSION['danger'] = 'Vehicle eliminat!';
        }
        header('Location: /vehicles'); exit;
    }

    public function notFound()
    {
        http_response_code(404);
        echo '404 - Not Found';
        exit;
    }
}