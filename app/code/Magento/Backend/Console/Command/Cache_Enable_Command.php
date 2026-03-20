<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for enabling cache
 *
 * @api
 * @since 100.0.2
 */
class Cache_Enable_Command extends Abstract_Cache_Set_Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->set_name('cache:enable');
        $this->set_description('Enables cache type(s)');
        parent::configure();
    }
    /**
     * Is enable cache
     *
     * @return bool
     */
    protected function is_enable()
    {
        return true;
    }
}