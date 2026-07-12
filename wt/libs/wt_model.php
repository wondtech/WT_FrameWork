<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Improved
 ************************************************************************/

namespace WT\LIBS;

abstract class Wt_Model
{

    protected static $pKey;
    protected static $tableName;
    protected static $tableSchema;

    const DATA_TYPE_INT  = \PDO::PARAM_INT;
    const DATA_TYPE_STR  = \PDO::PARAM_STR;
    const DATA_TYPE_FIL  = \PDO::PARAM_LOB;
    const DATA_TYPE_BOOL = \PDO::PARAM_BOOL;

    public function __construct() {}

    private static function getPDO(): \PDO
    {
        return Wt_DB::getInstance()->getPDO();
    }

    private function prepareValues(\PDOStatement $stmt): void
    {
        foreach (static::$tableSchema as $columnName => $type) {
            if (!property_exists($this, $columnName)) {
                continue;
            }
            $value = $this->$columnName;
            if ($value === null) {
                continue;
            }
            if ($type === self::DATA_TYPE_BOOL) {
                $value = $value ? 1 : 0;
            }
            $stmt->bindValue(':' . $columnName, $value, $type);
        }
    }

    private function buildNameParamSql(): string
    {
        $parts = [];
        foreach (static::$tableSchema as $columnName => $type) {
            if (!property_exists($this, $columnName)) {
                continue;
            }
            $value = $this->$columnName;
            if ($value === null) {
                continue;
            }
            if ($type === self::DATA_TYPE_BOOL) {
                $value = $value ? 1 : 0;
            }
            $parts[] = $columnName . ' = :' . $columnName;
        }
        return implode(', ', $parts);
    }

    private static function logError(string $context, \PDOException $e): void
    {
        error_log('[Wt_Model]' . ' [' . $context . '] ' . $e->getMessage());
    }

    private function wt_insert(bool $noUpdate = false): int|bool
    {
        $sql = 'INSERT INTO ' . static::$tableName . ' SET ' . $this->buildNameParamSql();
        try {
            $PDO  = self::getPDO();
            $stmt = $PDO->prepare($sql);
            $this->prepareValues($stmt);
            if ($noUpdate) {
                return $stmt->execute();
            }
            $stmt->execute();
            $newId = (int) $PDO->lastInsertId();
            if ($newId > 0) {
                $this->{static::$pKey} = $newId;
            }
            return $newId;
        } catch (\PDOException $e) {
            self::logError('wt_insert', $e);
            throw new \RuntimeException('Database error on insert.', 0, $e);
        }
    }

    private function wt_update(): bool
    {
        $sql = 'UPDATE ' . static::$tableName
            . ' SET '   . $this->buildNameParamSql()
            . ' WHERE ' . static::$pKey . ' = :__pkey';
        try {
            $stmt = self::getPDO()->prepare($sql);
            $this->prepareValues($stmt);
            $stmt->bindValue(':__pkey', $this->{static::$pKey});
            return $stmt->execute();
        } catch (\PDOException $e) {
            self::logError('wt_update', $e);
            throw new \RuntimeException('Database error on update.', 0, $e);
        }
    }

    public function wt_save(bool $noUpdate = false): int|bool
    {
        $pkeyMissing = !isset($this->{static::$pKey});
        return ($pkeyMissing || $noUpdate)
            ? $this->wt_insert($noUpdate)
            : $this->wt_update();
    }

    public static function wt_exists(int|string $pKey, bool $str = false): bool
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$tableName
            . ' WHERE ' . static::$pKey . ' = :pKey';
        try {
            $stmt = self::getPDO()->prepare($sql);
            $stmt->bindValue(
                ':pKey',
                $pKey,
                $str ? self::DATA_TYPE_STR : self::DATA_TYPE_INT
            );
            $stmt->execute();
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            self::logError('wt_exists', $e);
            throw new \RuntimeException('Database error on exists check.', 0, $e);
        }
    }

    public static function wt_countData(
        ?string $SQL      = null,
        array   $bindings = [],
        ?int    $items    = null
    ): int {
        $sql = 'SELECT COUNT(*) FROM ' . static::$tableName;
        if (!empty($SQL)) {
            $sql .= ' ' . $SQL;
        }
        try {
            $stmt = self::getPDO()->prepare($sql);
            foreach ($bindings as $param => [$type, $value]) {
                $stmt->bindValue($param, $value, $type);
            }
            $stmt->execute();
            $count = (int) $stmt->fetchColumn();
            return empty($items) ? $count : (int) ceil($count / $items);
        } catch (\PDOException $e) {
            self::logError('wt_countData', $e);
            throw new \RuntimeException('Database error on count.', 0, $e);
        }
    }

    public static function wt_getData(
        ?string $SQL      = null,
        array   $bindings = [],
        ?int    $items    = null,
        ?int    $page     = null
    ): array {
        $sql = 'SELECT * FROM ' . static::$tableName;
        if (!empty($SQL)) {
            $sql .= ' ' . $SQL;
        }
        if (!empty($items)) {
            $currentPage = max(1, (int) $page);
            $offset      = ($currentPage - 1) * $items;
            $sql        .= ' LIMIT ' . $offset . ', ' . $items;
        }
        try {
            $stmt = self::getPDO()->prepare($sql);
            foreach ($bindings as $param => [$type, $value]) {
                $stmt->bindValue($param, $value, $type);
            }
            $stmt->execute();
            $results = $stmt->fetchAll(
                \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
                get_called_class()
            );
            return !empty($results) ? $results : [];
        } catch (\PDOException $e) {
            self::logError('wt_getData', $e);
            throw new \RuntimeException('Database error on select.', 0, $e);
        }
    }

    public static function wt_getByPkey(int|string $pKey, bool $str = false): static|false
    {
        $sql = 'SELECT * FROM ' . static::$tableName
            . ' WHERE ' . static::$pKey . ' = :pKey';
        try {
            $stmt = self::getPDO()->prepare($sql);
            $stmt->bindValue(
                ':pKey',
                $pKey,
                $str ? self::DATA_TYPE_STR : self::DATA_TYPE_INT
            );
            if ($stmt->execute() === true) {
                $results = $stmt->fetchAll(
                    \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
                    get_called_class()
                );
                return !empty($results) ? array_shift($results) : false;
            }
            return false;
        } catch (\PDOException $e) {
            self::logError('wt_getByPkey', $e);
            throw new \RuntimeException('Database error on select.', 0, $e);
        }
    }

    public static function wt_transaction(callable $callback): bool
    {
        $PDO = self::getPDO();
        $PDO->beginTransaction();
        try {
            $callback();
            $PDO->commit();
            return true;
        } catch (\Throwable $e) {
            $PDO->rollBack();
            error_log('[Wt_Model] Transaction rolled back: ' . $e->getMessage());
            throw $e;
        }
    }

    public function wt_delete(bool $str = false): bool
    {
        $sql = 'DELETE FROM ' . static::$tableName
            . ' WHERE ' . static::$pKey . ' = :pKey';
        try {
            $stmt = self::getPDO()->prepare($sql);
            $stmt->bindParam(
                ':pKey',
                $this->{static::$pKey},
                $str ? self::DATA_TYPE_STR : self::DATA_TYPE_INT
            );
            return $stmt->execute();
        } catch (\PDOException $e) {
            self::logError('wt_delete', $e);
            throw new \RuntimeException('Database error on delete.', 0, $e);
        }
    }

    public static function wt_deleteByPkey(int|string $pKey, bool $str = false): bool
    {
        $sql = 'DELETE FROM ' . static::$tableName
            . ' WHERE ' . static::$pKey . ' = :pKey';
        try {
            $stmt = self::getPDO()->prepare($sql);
            $stmt->bindValue(
                ':pKey',
                $pKey,
                $str ? self::DATA_TYPE_STR : self::DATA_TYPE_INT
            );
            return $stmt->execute();
        } catch (\PDOException $e) {
            self::logError('wt_deleteByPkey', $e);
            throw new \RuntimeException('Database error on delete.', 0, $e);
        }
    }
}