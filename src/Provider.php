<?php

namespace Temori\Distancexport;

use Temori\Distancexport\DataBases\BaseDriver;

/**
 * Class Provider
 *
 * @package Temori\Distancexport
 */
class Provider
{
    /**
     * @var array
     */
    protected $hosts = [
        'DX_DESTINATION_DB_DRIVER',
        'DX_DESTINATION_DB_HOST',
        'DX_DESTINATION_DB_PORT',
        'DX_DESTINATION_DB_DATABASE',
        'DX_DESTINATION_DB_USERNAME',
        'DX_DESTINATION_DB_PASSWORD',

        'DX_SOURCE_DB_DRIVER',
        'DX_SOURCE_DB_HOST',
        'DX_SOURCE_DB_PORT',
        'DX_SOURCE_DB_DATABASE',
        'DX_SOURCE_DB_USERNAME',
        'DX_SOURCE_DB_PASSWORD',
    ];

    /**
     * @var \Temori\Distancexport\DataBases\BaseDriver
     */
    protected $destination;

    /**
     * @var \Temori\Distancexport\DataBases\BaseDriver
     */
    protected $source;

    /**
     * Provider constructor.
     *
     * @param \Temori\Distancexport\DataBases\BaseDriver|null $destination
     * @param \Temori\Distancexport\DataBases\BaseDriver|null $source
     * @throws \Exception
     */
    public function __construct(BaseDriver $destination = null, BaseDriver $source = null)
    {
        // Throw an exception if everything is undefined.
        foreach ($this->hosts as $host) {
            if (!defined($host)) {
                throw new \Exception($this->errors());
            }
        }

        if ($destination) {
            $this->destination = new $destination;
        } else {
            $class_name = '\Temori\Distancexport\DataBases\Drivers\\' . ucfirst(DX_DESTINATION_DB_DRIVER);
            $this->destination = new $class_name('destination');
        }
        if ($source) {
            $this->source = new $source;
        } else {
            $class_name = '\Temori\Distancexport\DataBases\Drivers\\' . ucfirst(DX_SOURCE_DB_DRIVER);
            $this->source = new $class_name('source');
        }
    }

    /**
     * Get db drivers.
     *
     * @param $driver
     * @return mixed
     */
    public function getDbDriver($driver)
    {
        return $this->{$driver};
    }

    /**
     * Return Exception messages
     *
     * @return string
     */
    protected function errors()
    {
        return 'Host settings of Database are undefined.'.PHP_EOL.
            'You need to define below.'.PHP_EOL.implode(PHP_EOL, $this->hosts);
    }
}
