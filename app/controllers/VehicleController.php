<?php
require_once __DIR__ . '/../models/Vehicle.php';

class VehicleController
{
    private $vehicle;

    public function __construct()
    {
        // Controlador per les operacions CRUD sobre vehicles
        // Controller for CRUD operations on vehicles
        $this->vehicle = new Vehicle();
    }

    public function index()
    {
        $vehicles = $this->vehicle->getAll() ?: [];
        require_once __DIR__ . '/../views/admin/vehicles.php';
    }

    public function create()
    {
        // Mostra el formulari per crear un vehicle
        // Show the form to create a vehicle
        require_once __DIR__ . '/../views/admin/vehicle_create.php';
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
        // Normalize empty strings to null to avoid inserting empty strings into the DB
        foreach ($data as $k => $v) {
            if ($v === '') $data[$k] = null;
        }
        
        $result = $this->vehicle->create($data);
        if ($result === false) {
            // Error en persistència: mostrem missatge d'error i re-obrim el formulari
            // Persistence error: show error message and re-open the form
            $_SESSION['error'] = 'Error en crear el vehicle. Revisa dades i format.';
            $this->create();
            return;
        }

        // Insert correcte: flash de success i redirecció
        // Successful insert: set success flash and redirect
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
        // Load edit view with the vehicle data
        require_once __DIR__ . '/../views/admin/vehicle_edit.php';
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
        // Normalize and update, then set a warning-style flash
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
            // Delete and set a danger flash to indicate deletion
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