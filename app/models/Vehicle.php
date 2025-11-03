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
        // Return rows with location converted to WKT for display
        try {
            $sql = "SELECT *, ST_AsText(location) as location FROM `{$this->table}` WHERE tenant_id = :tenant_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['tenant_id' => $this->tenantId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Convert POINT(lon lat) -> POINT(lat lon) for display consistency with forms
            foreach ($rows as &$r) {
                if (!empty($r['location']) && preg_match('/POINT\s*\(([-0-9\.]+)\s+([-0-9\.]+)\)/', $r['location'], $m)) {
                    $lon = (float)$m[1];
                    $lat = (float)$m[2];
                    $r['location'] = sprintf('POINT(%F %F)', $lat, $lon);
                }
            }
            return $rows;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    public function findById(int $id): array|false
    {
        try {
            $sql = "SELECT *, ST_AsText(location) as location FROM `{$this->table}` WHERE id = :id AND tenant_id = :tenant_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id, 'tenant_id' => $this->tenantId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['location']) && preg_match('/POINT\s*\(([-0-9\.]+)\s+([-0-9\.]+)\)/', $row['location'], $m)) {
                $lon = (float)$m[1];
                $lat = (float)$m[2];
                $row['location'] = sprintf('POINT(%F %F)', $lat, $lon);
            }
            return $row ?: false;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
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