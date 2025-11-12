<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Vehicle.php';

class VehiclesApiController extends Controller {
    public function __construct() { parent::__construct(); }

    public function list(): void {
        if (empty($_SESSION['tenant_id'])) {
            $this->json(['error' => 'tenant_not_set'], 400);
            return;
        }
        $tenantId = (int)$_SESSION['tenant_id'];
        $statuses = [];
        if (!empty($_GET['status'])) {
            $statuses = array_filter(array_map('trim', explode(',', (string)$_GET['status'])));
        }
        $vehModel = new Vehicle($tenantId);
        $bounds = null;
        if (isset($_GET['south'], $_GET['west'], $_GET['north'], $_GET['east'])) {
            $south = (float)$_GET['south'];
            $west  = (float)$_GET['west'];
            $north = (float)$_GET['north'];
            $east  = (float)$_GET['east'];
            if ($south <= $north && $west <= $east) {
                $bounds = [$south, $west, $north, $east];
            }
        }
        if ($bounds) {
            $vehicles = $vehModel->listWithinBounds($bounds[0], $bounds[1], $bounds[2], $bounds[3], $statuses);
        } else {
            $vehicles = $vehModel->listAll($statuses);
        }
        $this->json(['vehicles' => $vehicles, 'ts' => time()]);
    }
}

?>