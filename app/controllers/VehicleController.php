<?php
require_once __DIR__ . '/../models/Vehicle.php';

class VehicleController
{
    private $vehicle;

    public function __construct()
    {
        $this->vehicle = new Vehicle();
    }

    public function index()
    {
        $vehicles = $this->vehicle->getAll() ?: [];
        require_once __DIR__ . '/../views/admin/vehicles.php';
    }

    public function create()
    {
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

        foreach ($data as $k => $v) {
            if ($v === '') $data[$k] = null;
        }
        
        $result = $this->vehicle->create($data);
        if ($result === false) {
            $_SESSION['error'] = 'Error en crear el vehicle. Revisa dades i format.';
            $this->create();
            return;
        }

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
        foreach ($data as $k => $v) if ($v === '') $data[$k] = null;

        $this->vehicle->update($id, $data);
        // Use a warning-style flash for edits (yellow)
        $_SESSION['warning'] = 'Vehicle actualitzat.';
        header('Location: /vehicles'); exit;
    }

    public function delete()
    {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id > 0) {
            $this->vehicle->delete($id);
            // Use danger flash for deletions (red)
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