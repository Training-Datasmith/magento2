<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Console;

/**
 * Contains a list of Console commands
 * @api
 * @since 100.0.2
 */
interface Command_List_Interface
{
    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function get_commands();
}