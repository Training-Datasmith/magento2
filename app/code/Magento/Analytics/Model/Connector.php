<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Object_Manager_Interface;
/**
 * A connector to external services.
 *
 * Aggregates and executes commands which perform requests to external services.
 */
class Connector
{
    public function __construct(
        /**
         * A list of possible commands.
         *
         * An associative array in format: 'command_name' => 'command_class_name'.
         *
         * The list may be configured in each module via '/etc/di.xml'.
         */
        private array $commands,
        private readonly Object_Manager_Interface $object_manager
    )
    {
    }
    /**
     * Executes a command in accordance with the given name.
     *
     * @param string $commandName
     * @return bool
     * @throws NotFoundException if the command is not found.
     */
    public function execute($command_name)
    {
        if (!array_key_exists($command_name, $this->commands)) {
            throw new Not_Found_Exception(__('Command "%1" was not found.', $command_name));
        }
        /** @var \Magento\Analytics\Model\Connector\CommandInterface $command */
        $command = $this->object_manager->create($this->commands[$command_name]);
        return $command->execute();
    }
}