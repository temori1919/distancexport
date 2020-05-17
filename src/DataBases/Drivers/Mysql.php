<?php

namespace Temori\Distancexport\DataBases\Drivers;

use Temori\Distancexport\DataBases\BaseDriver;
use Temori\Distancexport\DataBases\Connect;

/**
 * Class Mysql
 * Used when the driver is Mysql.
 *
 * @package Temori\Distancexport\DataBases\Drivers
 */
class Mysql extends Connect implements BaseDriver
{
    /**
     * Mysql constructor.
     *
     * @param $type
     */
    public function __construct($type)
    {
        parent::__construct($type);
        // Ignore foregin keys temporarily.
        $stmt = $this->pdo->prepare('SET FOREIGN_KEY_CHECKS = 0;');
        $stmt->execute();
    }

    /**
     * Get Tables.
     *
     * @return array
     */
    public function getTables() : array
    {
        $db_name = $this->type === 'destination' ? DX_DESTINATION_DB_DATABASE : DX_SOURCE_DB_DATABASE;
        $stmt = $this->pdo->prepare('SHOW TABLES FROM ' . $db_name);

        $stmt->execute();
        return array_column($stmt->fetchAll(\PDO::FETCH_NUM), 0);
    }

    /**
     * Get columns.
     *
     * @param $table
     * @return array
     */
    public function getColumns($table) : array
    {
        $stmt = $this->pdo->prepare('SHOW COLUMNS FROM ' . $table);

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    /**
     * Get records.
     *
     * @param       $table
     * @param array $column
     * @return array
     */
    public function getRecoeds($table, $column = []) : array
    {
        $select = $column ? implode(',', $column) : '*';
        $stmt = $this->pdo->prepare("SELECT {$select} FROM {$table}");

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Db insert.
     *
     * @param $table
     * @param $columns
     * @param $values
     * @return bool
     */
    public function insert($table, $columns, $values)
    {
        $values = array_reduce($values, function ($carry, $value) {
            $value = '("' . implode('","', $value) . '")';
            if (!$carry) $carry = [];
            $carry[] = $value;
            return $carry;
        });

        $columns = implode(',', $columns);
        $values = implode(',', $values);

        $stmt = $this->pdo->prepare("
          INSERT INTO {$table} ({$columns}) 
          VALUES $values
        ");

        return $stmt->execute();
    }

    /**
     * Begin transaction.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit.
     *
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Rollback.
     *
     * @return bool
     */
    public function rollback()
    {
        return $this->pdo->rollback();
    }
}
