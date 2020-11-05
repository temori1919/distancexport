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
        $stmt = $this->pdo->query('SET FOREIGN_KEY_CHECKS = 0;');
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

        $stmt = $this->pdo->prepare(
            'SELECT table_name
                    FROM information_schema.tables    
                    WHERE table_type = \'BASE TABLE\' AND table_schema = ? 
                    ORDER BY table_name ASC'
        );

        $stmt->execute([$db_name]);
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
        $stmt = $this->pdo->query('SHOW COLUMNS FROM `' . $table . '`');

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
        $select = $column ? '`' . implode('``,`', $column) . '`' : '*';
        $stmt = $this->pdo->query("SELECT {$select} FROM `{$table}`");

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
        // Make placeholder array.
        $values_placeholder = array_reduce($values, function ($carry, $value) {
            $value = '(' . implode(',', array_keys($value)) . ')';
            if (!$carry) $carry = [];
            $carry[] = $value;
            return $carry;
        });
        $values_placeholder = implode(',', $values_placeholder);

        $values = array_reduce($values, function ($carry, $value) {
            return !$carry ? $value : array_merge($carry, $value);
        });

        $columns = '`' . implode('`,`', $columns) . '`';

        $stmt = $this->pdo->prepare("
          INSERT INTO `{$table}` ({$columns}) 
          VALUES {$values_placeholder}
        ");

        return $stmt->execute($values);
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
