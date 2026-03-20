<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu;

/**
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Director
{
    /**
     * Factory model
     * @var \Magento\Backend\Model\Menu\Builder\CommandFactory
     */
    protected $_command_factory;
    /**
     * @param \Magento\Backend\Model\Menu\Builder\CommandFactory $factory
     */
    public function __construct(\Magento\Backend\Model\Menu\Builder\Command_Factory $factory)
    {
        $this->_command_factory = $factory;
    }
    /**
     * Build menu instance
     *
     * @param array $config
     * @param \Magento\Backend\Model\Menu\Builder $builder
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    abstract public function direct(array $config, \Magento\Backend\Model\Menu\Builder $builder, \Psr\Log\Logger_Interface $logger);
}