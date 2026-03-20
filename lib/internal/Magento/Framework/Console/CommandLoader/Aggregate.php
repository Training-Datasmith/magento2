<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Console\Command_Loader;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command_Loader\Command_Loader_Interface;
use Symfony\Component\Console\Exception\Command_Not_Found_Exception;
/**
 * Class Aggregate has a list of command loaders, which can be extended via DI configuration.
 */
class Aggregate implements Command_Loader_Interface
{
    /** @var CommandLoaderInterface[] */
    private array $command_loaders;
    /**
     * @param array $commandLoaders
     */
    public function __construct(array $command_loaders = [])
    {
        $this->command_loaders = $command_loaders;
    }
    /**
     * Intiantiate and return the command referred to by $name within the internal command loaders.
     *
     * If $name does not refer to a command, throw a CommandNotFoundException.
     *
     * @param string $name
     * @return Command
     * @throws CommandNotFoundException
     */
    public function get(string $name): Command
    {
        foreach ($this->command_loaders as $command_loader) {
            if ($command_loader->has($name)) {
                return $command_loader->get($name);
            }
        }
        throw new Command_Not_Found_Exception(sprintf('Command "%s" does not exist.', $name));
    }
    /**
     * Return whether $name refers to a command within the internal command loaders.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        foreach ($this->command_loaders as $command_loader) {
            if ($command_loader->has($name)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Return an array of all the command names provided by the internal command loaders.
     *
     * @return string[]
     */
    public function get_names(): array
    {
        return array_merge([], ...array_map(static function (Command_Loader_Interface $command_loader) {
            return $command_loader->get_names();
        }, $this->command_loaders));
    }
}