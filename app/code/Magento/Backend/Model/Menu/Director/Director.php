<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Menu\Director;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu\Builder\Abstract_Command;
use Psr\Log\Logger_Interface;
/**
 * @api
 * @since 100.0.2
 */
class Director extends \Magento\Backend\Model\Menu\Abstract_Director
{
    /**
     * Log message patterns
     *
     * @var array
     */
    protected $_message_patterns = ['update' => 'Item %s was updated', 'remove' => 'Item %s was removed'];
    /**
     * Get command object
     *
     * @param array $data command params
     * @param LoggerInterface $logger
     * @return AbstractCommand
     */
    protected function _get_command($data, $logger)
    {
        $command = $this->_command_factory->create($data['type'], ['data' => $data]);
        if (isset($this->_message_patterns[$data['type']])) {
            $logger->debug(sprintf($this->_message_patterns[$data['type']], $command->get_id()));
        }
        return $command;
    }
    /**
     * Build menu instance
     *
     * @param array $config
     * @param Builder $builder
     * @param LoggerInterface $logger
     * @return void
     */
    public function direct(array $config, Builder $builder, Logger_Interface $logger)
    {
        foreach ($config as $data) {
            $builder->process_command($this->_get_command($data, $logger));
        }
    }
}