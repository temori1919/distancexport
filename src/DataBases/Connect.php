<?php

namespace Temori\Distancexport\DataBases;

/**
 * Class Connect
 *
 * @package Temori\Distancexport\DataBases
 */
class Connect
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var
     */
    protected $type;

    /**
     * Connect constructor.
     *
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;

        try {
            if ($type === 'destination') {
                $dns = DX_DESTINATION_DB_DRIVER . ':host=' . DX_DESTINATION_DB_HOST .
                    ';port=' . DX_DESTINATION_DB_PORT . ';dbname=' . DX_DESTINATION_DB_DATABASE;
                $this->pdo = new \PDO($dns, DX_DESTINATION_DB_USERNAME, DX_DESTINATION_DB_PASSWORD);
            } else if ($type === 'source') {
                $dns = DX_SOURCE_DB_DRIVER . ':host=' . DX_SOURCE_DB_HOST .
                    ';port=' . DX_SOURCE_DB_PORT . ';dbname=' . DX_SOURCE_DB_DATABASE;
                $this->pdo = new \PDO($dns, DX_SOURCE_DB_USERNAME, DX_SOURCE_DB_PASSWORD);
            }

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION);

            // Only mysql driver.
            if (DX_DESTINATION_DB_DRIVER === 'mysql') {
                $this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
            }
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
