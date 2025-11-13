<?php

class Model
{
    protected $pdo;
    protected $table;
    protected $tenantId;

    public function __construct(string $table, int $tenantId)
    {
        // load DB config which exposes $pdo
        require_once __DIR__ . '/../../config/database.php';

        if (!isset($pdo) || !($pdo instanceof PDO)) {
            throw new RuntimeException('PDO instance not available from config/database.php');
        }

        // basic whitelist for table names (prevents SQL injection via table names)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name');
        }

        $this->pdo = $pdo;
        $this->table = $table;
        $this->tenantId = $tenantId;
    }

    // Generic SELECT with tenant filter. $conditions is associative: ['col' => value, ...]
    public function find(array $conditions = []): array|false
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE tenant_id = :tenant_id";
        $params = ['tenant_id' => $this->tenantId];

        $i = 0;
        foreach ($conditions as $col => $val) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) continue;
            $i++;
            $ph = "p{$i}";
            $sql .= " AND `{$col}` = :{$ph}";
            $params[$ph] = $val;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    // Insert: $data is associative. tenant_id is injected automatically.
    public function insert(array $data): int|false
    {
        $data['tenant_id'] = $this->tenantId;
        // filter keys
        $cols = array_values(array_filter(array_keys($data), fn($c) => preg_match('/^[a-zA-Z0-9_]+$/', $c)));
        if (empty($cols)) return false;

        $placeholders = [];
        $params = [];
        foreach ($cols as $idx => $col) {
            $ph = "p{$idx}";
            // Special-case geometry column 'location' -> use ST_GeomFromText()
            if ($col === 'location') {
                $placeholders[] = "ST_GeomFromText(:{$ph})";
                // Expect WKT like 'POINT(lon lat)'
                $params[$ph] = $data[$col];
            } else {
                $placeholders[] = ":{$ph}";
                $params[$ph] = $data[$col];
            }
            $cols[$idx] = "`{$col}`";
        }

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            $this->table,
            implode(', ', $cols),
            implode(', ', $placeholders)
        );

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return (int)$this->pdo->lastInsertId();
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    // Update a row by id (and tenant_id)
    public function update(int $id, array $data): bool
    {
        if (empty($data)) return false;

        $set = [];
        $params = [];
        $i = 0;
        foreach ($data as $col => $val) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) continue;
            $ph = "p{$i}";
            if ($col === 'location') {
                $set[] = "`{$col}` = ST_GeomFromText(:{$ph})";
                $params[$ph] = $val;
            } else {
                $set[] = "`{$col}` = :{$ph}";
                $params[$ph] = $val;
            }
            $i++;
        }

        if (empty($set)) return false;

        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $set) . " WHERE id = :id AND tenant_id = :tenant_id";
        $params['id'] = $id;
        $params['tenant_id'] = $this->tenantId;

        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    // Delete a row by id (scoped to tenant)
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM `{$this->table}` WHERE id = :id AND tenant_id = :tenant_id";
        try {
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id, 'tenant_id' => $this->tenantId]);
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    // Simple join helper: returns rows from this table (a) joined with $joinTable (b).
    // $foreignKey = column in this table that references joinTable.id
    public function findWithJoin(string $joinTable, string $foreignKey, array $conditions = []): array|false
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $joinTable) || !preg_match('/^[a-zA-Z0-9_]+$/', $foreignKey)) {
            return false;
        }

        $sql = "SELECT a.*, b.* FROM `{$this->table}` a
                JOIN `{$joinTable}` b ON a.`{$foreignKey}` = b.id
                WHERE a.tenant_id = :tenant_id";

        $params = ['tenant_id' => $this->tenantId];
        $i = 0;
        foreach ($conditions as $col => $val) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $col)) continue;
            $i++;
            $ph = "p{$i}";
            $sql .= " AND a.`{$col}` = :{$ph}";
            $params[$ph] = $val;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logError($e);
            return false;
        }
    }

    protected function logError(Throwable $e): void
    {
        $msg = sprintf("[%s] %s in %s:%s\n", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine());
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
        @file_put_contents($logDir . '/model_errors.log', $msg, FILE_APPEND);
    }
}
?>