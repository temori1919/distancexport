<?php

namespace Temori\Test;

use PHPUnit\Framework\TestCase;
use Temori\Distancexport\Distancexport;

/**
 * Class DistancexportTest
 *
 * @package Temori\Test
 */
class DistancexportTest extends TestCase
{
    /**
     * @var null
     */
    private $conn_destination = null;
    /**
     * @var null
     */
    private $conn_source = null;
    /**
     * @var array
     */
    protected $record = [
        'users' => [
            'column' => ['id', 'name', 'created_at'],
            'values' => [
                [1, 'test1',  '2020-10-15 01:15:00'],
                [2, 'test2',  '2020-10-15 02:20:00'],
            ]
        ],
        'posts' => [
            'column' => ['id', 'user_id', 'context', 'created_at'],
            'values' => [
                [1, 1, 'posts1', '2020-10-15 03:30:00'],
                [2, 2, 'posts2', '2020-10-15 04:40:00'],
            ]
        ],
        'comment' => [
            'column' => ['id', 'post_id', 'context', 'created_at'],
            'values' => [
                [1, 1, 'comment1', '2020-10-15 05:50:00'],
                [2, 2, 'comment2', '2020-10-15 06:00:00'],
            ]
        ]
    ];

    /**
     * Setup before run.
     */
    public function setUp()
    {
        // Load for phpdotenv.
        $dotenv = \Dotenv\Dotenv::create(__DIR__ . '/..', '.env');
        $dotenv->load();

        // define database settings.
        define('DX_DESTINATION_DB_DRIVER', getenv('DX_DESTINATION_DB_DRIVER'));
        define('DX_DESTINATION_DB_HOST', getenv('DX_DESTINATION_DB_HOST'));
        define('DX_DESTINATION_DB_PORT', getenv('DX_DESTINATION_DB_PORT'));
        define('DX_DESTINATION_DB_DATABASE', getenv('DX_DESTINATION_DB_DATABASE'));
        define('DX_DESTINATION_DB_USERNAME', getenv('DX_DESTINATION_DB_USERNAME'));
        define('DX_DESTINATION_DB_PASSWORD', getenv('DX_DESTINATION_DB_PASSWORD'));

        define('DX_SOURCE_DB_DRIVER', getenv('DX_SOURCE_DB_DRIVER'));
        define('DX_SOURCE_DB_HOST', getenv('DX_SOURCE_DB_HOST'));
        define('DX_SOURCE_DB_PORT', getenv('DX_SOURCE_DB_PORT'));
        define('DX_SOURCE_DB_DATABASE', getenv('DX_SOURCE_DB_DATABASE'));
        define('DX_SOURCE_DB_USERNAME', getenv('DX_SOURCE_DB_USERNAME'));
        define('DX_SOURCE_DB_PASSWORD', getenv('DX_SOURCE_DB_PASSWORD'));

        $this->setConnecton('destination');
        $this->setConnecton('source');

        // Set database Before run test.
        $this->databaseSetUp();
        // Add records.
        $this->addRecord();
    }

    /**
     * Clean up traces.
     */
    public function tearDown()
    {
        $this->setConnecton('destination');
        $this->setConnecton('source');

        $this->conn_destination->query('DROP DATABASE ' . DX_DESTINATION_DB_DATABASE);
        $this->conn_source->query('DROP DATABASE ' . DX_SOURCE_DB_DATABASE);
    }

    /**
     * Run test.
     *
     * @throws \Exception
     */
    public function testInit()
    {
        $dis = new Distancexport;

        $params = [];
        foreach ($this->record as $table => $column) {
            foreach ($column['column'] as $c) {
                $params[] = [
                    0 => $table . ' - ' . $c,
                    7 => $table . ' - ' . $c,
                    13 => '',
                ];
            }
            // Insert divider for table.
            $params[] = [
                0 => '',
                7 => '',
                13 => '',
            ];
        }

        $_POST['runtype'] = 'run';
        $_POST['params'] = json_encode($params);

        $dis->init();

        foreach ($this->record as $table => $colmun) {
            $destination = $this->getRecord($table, 'conn_destination');
            $source = $this->getRecord($table, 'conn_source');

            // Run test.
            $this->assertEquals($destination, $source);
        }
    }

    /**
     * Setup databses before run.
     */
    protected function databaseSetUp()
    {
        $this->conn_destination->query('CREATE DATABASE ' . DX_DESTINATION_DB_DATABASE);
        $this->conn_source->query('CREATE DATABASE ' . DX_SOURCE_DB_DATABASE);

        $this->setConnecton('destination', true);
        $this->setConnecton('source', true);

        foreach (['conn_destination', 'conn_source'] as $pdo) {
            $data_type = $pdo === 'conn_destination' ? 'INTEGER' : 'INT';

            $this->{$pdo}->query(
                'CREATE TABLE users 
              (id ' . $data_type . ' NOT NULL, name VARCHAR(255), created_at TIMESTAMP, PRIMARY KEY(id))'
            );
            $this->{$pdo}->query(
                'CREATE TABLE posts
              (id ' . $data_type . ' NOT NULL, user_id INT NOT NULL, context TEXT NOT NULL, created_at TIMESTAMP, PRIMARY KEY(id))'
            );
            $this->{$pdo}->query(
                'CREATE TABLE comment
              (id ' . $data_type . ' NOT NULL, post_id INT NOT NULL, context TEXT NOT NULL, created_at TIMESTAMP, PRIMARY KEY(id))'
            );
        }
    }

    /**
     * Connect to databases.
     *
     * @param      $type
     * @param bool $selected_db
     */
    protected function setConnecton($type, $selected_db = false)
    {
        $upper_str = strtoupper($type);
        $dns = constant('DX_' . $upper_str . '_DB_DRIVER') . ':host=' .
            constant('DX_' . $upper_str . '_DB_HOST') . ';port=' .
            constant('DX_' . $upper_str . '_DB_PORT');
        if ($this->{'conn_' . $type} === null) {
            $this->{'conn_' . $type} = new \PDO($dns, constant('DX_' . $upper_str . '_DB_USERNAME'), constant('DX_' . $upper_str . '_DB_PASSWORD'));
        } else if ($selected_db) {
            $this->{'conn_' . $type} = null;
            $dns .= ';dbname=' . constant('DX_' . $upper_str . '_DB_DATABASE');
            $this->{'conn_' . $type} = new \PDO($dns, constant('DX_' . $upper_str . '_DB_USERNAME'), constant('DX_' . $upper_str . '_DB_PASSWORD'));
        }
    }

    /**
     * Insert record.
     */
    protected function addRecord()
    {
        foreach ($this->record as $teable => $item) {
            $column = implode(',', $item['column']);
            $placeholder = [];
            $replacement = [];
            foreach ($item['values'] as $value) {
                $count = count($value);
                $placeholder[] = '(' . implode(',', array_fill(0, $count, '?')) . ')';
                $replacement = array_merge($replacement, $value);
            }

            $placeholder = implode(',', $placeholder);

            // Insert datas.
            $sql = "INSERT INTO {$teable} ({$column})
                VALUES {$placeholder}";

            $stmt = $this->conn_source->prepare($sql);
            $stmt->execute($replacement);
        }
    }

    /**
     * Get record.
     */
    protected function getRecord($table, $type)
    {
        $sql = "SELECT * FROM {$table}";

        $stmt = $this->{$type}->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
