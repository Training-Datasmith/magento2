<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Autoload;

use InvalidArgumentException;
/**
 * Registry to store a static member autoloader
 */
class Autoloader_Registry
{
    /**
     * @var AutoloaderInterface
     */
    protected static $autoloader;
    /**
     * Registers the given autoloader as a static member
     *
     * @param AutoloaderInterface $newAutoloader
     * @return void
     */
    public static function register_autoloader(Autoloader_Interface $new_autoloader): void
    {
        self::$autoloader = $new_autoloader;
    }
    /**
     * Returns the registered autoloader
     *
     * @throws InvalidArgumentException
     * @return AutoloaderInterface
     */
    public static function get_autoloader(): Autoloader_Interface
    {
        if (!self::$autoloader instanceof Autoloader_Interface) {
            throw new InvalidArgumentException('Autoloader is not registered, cannot be retrieved.');
        }
        return self::$autoloader;
    }
}