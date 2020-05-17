<?php

namespace Temori\Distancexport\DataBases;


/**
 * Interface BaseDriver
 *
 * @package Temori\Distancexport\DataBases
 */
interface BaseDriver
{
    /**
     * @return array
     */
    public function getTables() : array ;

    /**
     * @param $table
     * @return array
     */
    public function getColumns($table) : array;

    /**
     * @param       $table
     * @param array $column
     * @return array
     */
    public function getRecoeds($table, $column = []) : array;

    /**
     * @param $table
     * @param $columns
     * @param $values
     * @return mixed
     */
    public function insert($table, $columns, $values);

    /**
     * @return mixed
     */
    public function beginTransaction();

    /**
     * @return mixed
     */
    public function commit();

    /**
     * @return mixed
     */
    public function rollback();
}
