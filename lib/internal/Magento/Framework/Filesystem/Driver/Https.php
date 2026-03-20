<?php

declare (strict_types=1);
/**
 * Origin filesystem driver
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Filesystem\Driver;

/**
 * Class Https
 *
 */
class Https extends Http
{
    /**
     * Scheme distinguisher
     *
     * @var string
     */
    protected $scheme = 'https';
    /**
     * Parse a https url
     *
     * @param string $path
     * @return array
     */
    protected function parse_url($path)
    {
        $url_prop = parent::parse_url($path);
        if (!isset($url_prop['port'])) {
            $url_prop['port'] = 443;
        }
        return $url_prop;
    }
    /**
     * Open a https url
     *
     * @param string $hostname
     * @param int $port
     * @return array
     */
    protected function open($hostname, $port)
    {
        return parent::open('ssl://' . $hostname, $port);
    }
}