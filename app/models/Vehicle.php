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
            $this->logError($e);
            return [];
        }
    }

    // List vehicles within a bounding box [south, west, north, east]
    public function listWithinBounds(float $south, float $west, float $north, float $east, array $statuses = []): array {
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
            $this->logError($e);
            return [];
        }
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
