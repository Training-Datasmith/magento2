<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Console\Command;

/**
 * Command for disabling cache
 *
 * @api
 * @since 100.0.2
 */
class Cache_Disable_Command extends Abstract_Cache_Set_Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->set_name('cache:disable');
        $this->set_description('Disables cache type(s)');
        parent::configure();
    }
    /**
     * Is Disable cache
     *
     * @return bool
     */
    protected function is_enable()
    {
        return false;
    }
}