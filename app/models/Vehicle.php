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
        // Return rows; location is stored as plain text "lat lon"
        try {
            $sql = "SELECT * FROM `{$this->table}` WHERE tenant_id = :tenant_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['tenant_id' => $this->tenantId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Normalize location into "lat lon" for display
            foreach ($rows as &$r) {
                if (!empty($r['location'])) {
                    if (preg_match('/POINT\s*\(([-0-9\.]+)\s+([-0-9\.]+)\)/i', $r['location'], $m)) {
                        $lon = (float)$m[1];
                        $lat = (float)$m[2];
                        $r['location'] = sprintf('%F %F', $lat, $lon);
                    } elseif (preg_match('/^\s*([-0-9\.]+)\s+[ ,]?\s*([-0-9\.]+)\s*$/', $r['location'], $m2)) {
                        $lat = (float)$m2[1];
                        $lon = (float)$m2[2];
                        $r['location'] = sprintf('%F %F', $lat, $lon);
                    }
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
            $sql = "SELECT * FROM `{$this->table}` WHERE id = :id AND tenant_id = :tenant_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id, 'tenant_id' => $this->tenantId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['location'])) {
                if (preg_match('/POINT\s*\(([-0-9\.]+)\s+([-0-9\.]+)\)/i', $row['location'], $m)) {
                    $lon = (float)$m[1];
                    $lat = (float)$m[2];
                    $row['location'] = sprintf('%F %F', $lat, $lon);
                } elseif (preg_match('/^\s*([-0-9\.]+)\s+[ ,]?\s*([-0-9\.]+)\s*$/', $row['location'], $m2)) {
                    $lat = (float)$m2[1];
                    $lon = (float)$m2[2];
                    $row['location'] = sprintf('%F %F', $lat, $lon);
                }
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

        // Normalize location input into plain text "lat lon"
        if (!empty($data['location'])) {
            $raw = trim($data['location']);
            $lat = $lon = null;
            if (preg_match('/POINT\s*\(([^)]+)\)/i', $raw, $m)) {
                $coords = preg_split('/[ ,]+/', trim($m[1]));
                if (count($coords) >= 2) {
                    $a = floatval($coords[0]);
                    $b = floatval($coords[1]);
                    if ($a < -90 || $a > 90) { $lon = $a; $lat = $b; }
                    else { $lat = $a; $lon = $b; }
                }
            } elseif (preg_match('/^\s*([-0-9\.]+)\s*[ ,]?\s*([-0-9\.]+)\s*$/', $raw, $m2)) {
                $lat = floatval($m2[1]);
                $lon = floatval($m2[2]);
            }

            if ($lat !== null && $lon !== null) {
                $data['location'] = sprintf('%F %F', $lat, $lon);
            } else {
                $data['location'] = null;
            }
        } else {
            $data['location'] = null;
        }

        error_log('CREATE VEHICLE DATA: ' . print_r($data, true));
        return $this->insert($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['tenant_id'] = 1;
        // Normalize location incoming in updates as well
        if (isset($data['location']) && !empty($data['location'])) {
            $raw = trim($data['location']);
            if (preg_match('/POINT\s*\(([^)]+)\)/i', $raw, $m)) {
                $coords = preg_split('/[ ,]+/', trim($m[1]));
                if (count($coords) >= 2) {
                    $a = floatval($coords[0]);
                    $b = floatval($coords[1]);
                    if ($a < -90 || $a > 90) { $lon = $a; $lat = $b; }
                    else { $lat = $a; $lon = $b; }
                    $data['location'] = sprintf('%F %F', $lat, $lon);
                }
            } elseif (preg_match('/^\s*([-0-9\.]+)\s*[ ,]?\s*([-0-9\.]+)\s*$/', $raw, $m2)) {
                $lat = floatval($m2[1]);
                $lon = floatval($m2[2]);
                $data['location'] = sprintf('%F %F', $lat, $lon);
            }
        }

        return parent::update($id, $data);
    }

    public function delete(int $id): bool
    {
        return parent::delete($id);
    }
}