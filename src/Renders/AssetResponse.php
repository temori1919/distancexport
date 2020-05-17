<?php

namespace Temori\Distancexport\Renders;


/**
 * Class AssetResponse
 *
 * @package Temori\Distancexport\Renders
 */
class AssetResponse
{
    /**
     * Render assets in html.
     *
     * @param $file
     * @return false|string
     */
    public static function assets($file)
    {
        return file_get_contents(__DIR__ . '/../../resources/assets/' . $file);
    }
}
