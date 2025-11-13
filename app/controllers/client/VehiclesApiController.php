<?php
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../models/Vehicle.php';

class VehiclesApiController extends Controller {
    public function __construct() { parent::__construct(); }

    public function list(): void {
        $tenantId = $this->resolveTenant();
        $statuses = $this->parseStatuses();
        $bounds = $this->parseBounds();
        $vehicles = $this->fetchVehicles($tenantId, $statuses, $bounds);
        $this->respondVehicles($vehicles);
    }

    // ---- Helpers ----
    private function resolveTenant(): int
    {
        $tenantId = method_exists('TenantContext', 'tenantId') ? (int)(TenantContext::tenantId() ?? 0) : 0;
        return $tenantId > 0 ? $tenantId : $this->requireTenant();
    }

    private function parseStatuses(): array
    {
        if (empty($_GET['status'])) return [];
        return array_filter(array_map('trim', explode(',', (string)$_GET['status'])));
    }

    private function parseBounds(): ?array
    {
        if (!isset($_GET['south'], $_GET['west'], $_GET['north'], $_GET['east'])) return null;
        $south = (float)$_GET['south'];
        $west  = (float)$_GET['west'];
        $north = (float)$_GET['north'];
        $east  = (float)$_GET['east'];
        if ($south <= $north && $west <= $east) {
            return [$south, $west, $north, $east];
        }
        return null;
    }

    private function fetchVehicles(int $tenantId, array $statuses, ?array $bounds): array
    {
        $vehModel = new Vehicle($tenantId);
        if ($bounds) {
            return $vehModel->listWithinBounds($bounds[0], $bounds[1], $bounds[2], $bounds[3], $statuses);
        }
        return $vehModel->listAll($statuses);
    }

    private function respondVehicles(array $vehicles): void
    {
        $this->json(['vehicles' => $vehicles, 'ts' => time()]);
    }
}

?>