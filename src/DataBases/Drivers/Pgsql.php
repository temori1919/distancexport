<?php

namespace Temori\Distancexport\DataBases\Drivers;

use Temori\Distancexport\DataBases\BaseDriver;
use Temori\Distancexport\DataBases\Connect;

/**
 * Class Pgsql
 * Used when the driver is PostgreSql.
 *
 * @package Temori\Distancexport\DataBases\Drivers
 */
class Pgsql extends Connect implements BaseDriver
{
    /**
     * Pgsql constructor.
     *
     * @param $type
     */
    public function __construct($type)
    {
        parent::__construct($type);
        // Disable all triggers only when during this session.
        $stmt = $this->pdo->query('SET session_replication_role = replica;');
        $stmt->execute();
    }

    /**
     * Get Tables.
     *
     * @return array
     */
    public function getTables(): array
    {
        $stmt = $this->pdo->query(
            'SELECT tablename 
                       FROM pg_tables 
                       WHERE tablename NOT LIKE \'pg_%\' 
                       AND schemaname NOT LIKE \'infomation_%\'
                       AND tablename NOT LIKE \'sql_%\'
                       ORDER BY tablename'
        );

        $stmt->execute();
        return array_column($stmt->fetchAll(\PDO::FETCH_NUM), 0);
    }

    /**
     * Get columns.
     *
     * @param $table
     * @return array
     */
    public function getColumns($table): array
    {
        $stmt = $this->pdo->query(
            'SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns
            WHERE table_name = ' . $this->pdo->quote($table)
        );

        // Get Primary key & foregin keys.
        return array_map(function ($item) use ($table) {
            $stmt_sub = $this->pdo->prepare(
                'SELECT tb_con.constraint_type
                FROM information_schema.table_constraints tb_con
                INNER JOIN information_schema.key_column_usage kcu
                        ON tb_con.constraint_catalog = kcu.constraint_catalog
                       AND tb_con.constraint_schema = kcu.constraint_schema
                       AND tb_con.constraint_name = kcu.constraint_name
                WHERE tb_con.table_name = ?
                  AND kcu.column_name = ?
            ');
            $stmt_sub->execute([$table, $item[0]]);
            $key = $stmt_sub->fetch(\PDO::FETCH_ASSOC);
            $key = isset($key['constraint_type']) ? $key['constraint_type'] : '';

            array_splice($item, 3, 0, $key);

            // postgresql is no support for Extra.
            array_push($item, 'no support.');

            return $item;
        }, $stmt->fetchAll(\PDO::FETCH_NUM));
    }

    /**
     * Get records.
     *
     * @param       $table
     * @param array $column
     * @return array
     */
    public function getRecoeds($table, $column = []): array
    {
        $select = $column ? implode(',', array_map(function ($item) {
            return $this->pdo->quote($item);
        }, $column)) : '*';

        $table = $this->pdo->quote($table);

        $stmt = $this->pdo->query("SELECT {$select} FROM {$table}");

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
        $columns = implode(',', $columns);

        // Make placeholder array.
        $values_placeholder = array_reduce($values, function ($carry, $value) {
            $value = '(' . implode(',', array_fill(0, count($value), '?')) . ')';
            if (!$carry) $carry = [];
            $carry[] = $value;
            return $carry;
        });
        $values_placeholder = implode(',', $values_placeholder);

        $values = array_reduce($values, function ($carry, $value) {
            return !$carry ? $value : array_merge($carry, $value);
        });

        $stmt = $this->pdo->prepare("
          INSERT INTO {$table} ({$columns}) 
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
