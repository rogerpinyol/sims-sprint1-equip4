<?php

require_once __DIR__ . '/../core/Model.php';

class Vehicle extends Model {
    public function __construct(int $tenantId) {
        parent::__construct('vehicles', $tenantId);
    }

    // Return all vehicles for this tenant with lat/lng extracted from POINT
    // Optional $statuses array to filter by status
    public function listAll(array $statuses = []): array {
        $params = [':tenant_id' => $this->tenantId];
        $sql = "SELECT id, vin, model, status, battery_capacity, ST_X(location) AS lng, ST_Y(location) AS lat, sensor_data, created_at
                FROM `vehicles`
                WHERE tenant_id = :tenant_id";
        if (!empty($statuses)) {
            $allowed = ['available','booked','maintenance','charging'];
            $filtered = array_values(array_intersect($allowed, array_map('strval', $statuses)));
            if (!empty($filtered)) {
                $phs = [];
                foreach ($filtered as $i => $st) { $phs[] = ":st{$i}"; $params[":st{$i}"] = $st; }
                $sql .= " AND status IN (" . implode(',', $phs) . ")";
            }
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) { $this->normalizeRow($r); }
            return $rows;
        } catch (Throwable $e) {
            // Already handled by Model::logError
            $this->logError($e);
            return [];
        }
    }

    /**
     * List vehicles within a bounding box [south, west, north, east]
     * @param float
     * @param float
     * @param float
     * @param float
     * @param array
     * @return array
     */
    public function listWithinBounds(float $south, float $west, float $north, float $east, array $statuses = []): array {
        /** @var float $south, $north, $west, $east */
        $params = [
            ':tenant_id' => $this->tenantId,
            ':south' => $south,
            ':north' => $north,
            ':west'  => $west,
            ':east'  => $east,
        ];
        $sql = "SELECT id, vin, model, status, battery_capacity, ST_X(location) AS lng, ST_Y(location) AS lat, sensor_data, created_at
                FROM `vehicles`
                WHERE tenant_id = :tenant_id
                  AND ST_Y(location) BETWEEN :south AND :north
                  AND ST_X(location) BETWEEN :west AND :east";
        if (!empty($statuses)) {
            $allowed = ['available','booked','maintenance','charging'];
            $filtered = array_values(array_intersect($allowed, array_map('strval', $statuses)));
            if (!empty($filtered)) {
                $phs = [];
                foreach ($filtered as $i => $st) { $phs[] = ":st{$i}"; $params[":st{$i}"] = $st; }
                $sql .= " AND status IN (" . implode(',', $phs) . ")";
            }
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) { $this->normalizeRow($r); }
            return $rows;
        } catch (Throwable $e) {
            // Already handled by Model::logError
            $this->logError($e);
            return [];
        }
    }

    public function getAll(): array|false {
        try {
            $sql = "SELECT id, vin, model, status, battery_capacity, ST_X(location) AS lng, ST_Y(location) AS lat, sensor_data, created_at
                    FROM `{$this->table}`
                    WHERE tenant_id = :tenant_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['tenant_id' => $this->tenantId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as &$r) { $this->normalizeRow($r); }
            return $rows;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    public function findById(int $id): array|false {
        try {
            $sql = "SELECT id, vin, model, status, battery_capacity, ST_X(location) AS lng, ST_Y(location) AS lat, sensor_data, created_at
                    FROM `{$this->table}`
                    WHERE id = :id AND tenant_id = :tenant_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id, 'tenant_id' => $this->tenantId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $this->normalizeRow($row);
            }
            return $row ?: false;
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    public function create(array $data): int|false {
        $data['tenant_id'] = $this->tenantId;

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

        return $this->insert($data);
    }

    public function update(int $id, array $data): bool {
        return parent::update($id, $data);
    }

    public function delete(int $id): bool {
        return parent::delete($id);
    }

    private function normalizeRow(array &$r): void {
        $r['lat'] = isset($r['lat']) ? (float)$r['lat'] : null;
        $r['lng'] = isset($r['lng']) ? (float)$r['lng'] : null;
        $r['battery_level'] = null;
        if (!empty($r['sensor_data'])) {
            $sd = json_decode((string)$r['sensor_data'], true);
            if (is_array($sd) && isset($sd['battery'])) {
                $r['battery_level'] = is_numeric($sd['battery']) ? (float)$sd['battery'] : null;
            }
        }
        unset($r['sensor_data']);
    }
}

?>
