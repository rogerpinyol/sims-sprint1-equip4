<?php
require_once __DIR__ . '/../core/Model.php';

class Vehicle extends Model
{
    public function __construct()
    {
        parent::__construct('vehicles', 1); // table + tenant_id
    }

    public function getAll(): array|false
    {
        return $this->find(); // already filtered by tenant_id
    }

    public function findById(int $id): array|false
    {
        $rows = $this->find(['id' => $id]);
        return $rows[0] ?? false;
    }

    public function create(array $data): int|false
    {
        $data['tenant_id'] = 1;

        // Normalize location input
        if (!empty($data['location'])) {
            if (preg_match('/POINT\s*\(([^)]+)\)/i', $data['location'], $m)) {
                $coords = preg_split('/[ ,]+/', trim($m[1]));
                if (count($coords) >= 2) {
                    $a = floatval($coords[0]);
                    $b = floatval($coords[1]);
                    if ($a < -90 || $a > 90) { $lon = $a; $lat = $b; }
                    else { $lat = $a; $lon = $b; }
                    $data['location'] = sprintf('POINT(%F %F)', $lon, $lat);
                } else {
                    $data['location'] = 'POINT(0 0)';
                }
            } else {
                $data['location'] = 'POINT(0 0)';
            }
        } else {
            $data['location'] = 'POINT(0 0)';
        }

        error_log('CREATE VEHICLE DATA: ' . print_r($data, true));
        return $this->insert($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['tenant_id'] = 1;
        return parent::update($id, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}