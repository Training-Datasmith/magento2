<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

use Magento\Framework\App\Cache\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input_Option;
/**
 * Abstract cache command
 *
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Cache_Command extends Command
{
    /**
     * Input option bootstrap
     */
    public const INPUT_KEY_BOOTSTRAP = 'bootstrap';
    /**
     * CacheManager
     *
     * @var Manager
     */
    protected $cache_manager;
    /**
     * Constructor
     *
     * @param Manager $cacheManager
     */
    public function __construct(Manager $cache_manager)
    {
        $this->cache_manager = $cache_manager;
        parent::__construct();
    }
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->add_option(self::INPUT_KEY_BOOTSTRAP, null, Input_Option::VALUE_REQUIRED, 'add or override parameters of the bootstrap');
    }
}