<?php

namespace Temori\Distancexport;

ini_set('memory_limit', -1);
ini_set('max_execution_time', -1);

use Temori\Distancexport\DataBases\BaseDriver;

/**
 * Class Distancexport
 *
 * @package Temori\Distancexport
 */
class Distancexport
{
    /**
     * @var \Temori\Distancexport\DataBases\BaseDriver
     */
    protected $destination_db;
    /**
     * @var \Temori\Distancexport\DataBases\BaseDriver
     */
    protected $source_db;

    /**
     * Distancexport constructor.
     *
     * @param \Temori\Distancexport\DataBases\BaseDriver|null $destination
     * @param \Temori\Distancexport\DataBases\BaseDriver|null $source
     * @throws \Exception
     */
    public function __construct(BaseDriver $destination = null, BaseDriver $source = null)
    {
        $boot = new Provider($destination, $source);

        $this->destination_db = $boot->getDbDriver('destination');
        $this->source_db = $boot->getDbDriver('source');
    }

    /**
     * Html render & submit.
     */
    public function init()
    {
        $destination_table = $this->destination_db->getTables();
        $source_table = $this->source_db->getTables();

        $destination_column = [];
        $source_column = [];
        $empty_column = [array_fill(0, 6, '')];
        $source_recoed = [];
        $destination_recoed = [];

        // Format data source.
        foreach ($source_table as $key => $table) {
            $row = array_map(function ($item) use ($table) {
                $item[0] = $table . ' - ' . $item[0];
                return $item;
            }, $this->source_db->getColumns($table));

            // Add partition of empty array.
            $source_column = $key ?
                array_merge($source_column, $empty_column, $row) :
                array_merge($source_column, $row);

            $source_recoed[$table] = $this->source_db->getRecoeds($table);
        }

        // Format data destination.
        foreach ($destination_table as $key => $table) {
            $row = array_map(function ($item) use ($table) {
                $item[0] = $table . ' - ' . $item[0];
                return $item;
            }, $this->destination_db->getColumns($table));

            $destination_column = $key ?
                array_merge($destination_column, $empty_column, $row) :
                array_merge($destination_column, $row);
        }

        // Merge source & destination.
        foreach ($destination_column as $key => &$destination) {
            if (isset($source_column[$key])) {
                $destination = array_merge($destination, [null], $source_column[$key]);
            }
        }

        // Submit.
        if (!empty($_POST)) {
            $target_table = '';
            $records = [];
            $uniformity = [];

            // Begin db transaction.
            $this->destination_db->beginTransaction();

            $params = json_decode($_POST['params'], true);

            // Key used to store error message at the time of last data insert.
            $last_key = '';

            $error_count = 0;

            foreach ($params as $key => &$param) {
                // Init error messages.
                $param[14] = '';

                // When a blank line comes,
                // all columns of the corresponding table have been acquired, so insert the data.
                if (!$param[0]) {
                    try {
                        $this->insertData($target_table, $records, $uniformity);
                    } catch (\Exception $e) {
                        $params[$key - 1][14] = $e->getMessage();
                        $error_count++;
                    } catch (\Throwable $e) {
                        $params[$key - 1][14] = $e->getMessage();
                        $error_count++;
                    }
                    // Init Table name & data.
                    $target_table = '';
                    $records = [];
                    $uniformity = [];
                    continue;
                }


                if (!$param[7] && !$param[13]) continue;

                $destination_str_arr = explode(' - ', $param[0]);

                // If there is a value in this cell,
                // that value will be inserted uniformly.
                if ($param[13]) {
                    $uniformity[$destination_str_arr[1]] = $param[13];
                } else {
                    $source_str_arr = explode(' - ', $param[7]);
                    if (!$target_table) {
                        $target_table = $destination_str_arr[0];
                    }

                    $rows = $this->source_db->getRecoeds($source_str_arr[0], [$source_str_arr[1]]);
                    if ($rows) {
                        $records[$destination_str_arr[1]] = array_column($rows, $source_str_arr[1]);
                    }
                }

                $last_key = $key;
            }

            // Insert last data.
            if ($target_table && $records) {
                try {
                    $this->insertData($target_table, $records, $uniformity);
                } catch (\Exception $e) {
                    $params[$last_key][14] = $e->getMessage();
                    $error_count++;
                } catch (\Throwable $e) {
                    $params[$last_key][14] = $e->getMessage();
                    $error_count++;
                }
            }

            if ($_POST['runtype'] === 'run' && !$error_count) {
                $this->destination_db->commit();
                $done = true;
            } else {
                if (!$error_count) {
                    $done = true;
                }
                $this->destination_db->rollback();
            }

            $destination_column = $params;
        }

        $destination_column = json_encode($destination_column);

        require __DIR__ . '/../resources/views/index.php';
    }

    /**
     * Exec db insert.
     *
     * @param $table
     * @param $records
     */
    public function insertData($table, $records, $uniformity)
    {
        $columns = array_keys($records);
        // Process the array so that it can be easily saved in db.
        $values = call_user_func_array('array_map', array_merge([null], $records));

        // Convert to multidimensional array,
        // if not valune is multidimensional arrays.
        $values = array_map(function ($item) {
            return is_array($item) ? $item : [$item];
        }, $values);

        // If exists uniform data.
        if ($uniformity) {
            $array_cnt = count($values);
            foreach ($uniformity as $key => $item) {
                for ($i = 0; $i < $array_cnt; $i++) {
                    $values[$i][$key] = $item;
                }
                $columns[] = $key;
            }
        }

        $this->destination_db->insert($table, $columns, $values);
    }
}
