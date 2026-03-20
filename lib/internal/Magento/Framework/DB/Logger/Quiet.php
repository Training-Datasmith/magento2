<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Logger;

class Quiet implements \Magento\Framework\DB\Logger_Interface
{
    /**
     * {@inheritdoc}
     */
    public function log($str)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function log_stats($type, $sql, $bind = [], $result = null)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function critical(\Exception $e)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function start_timer()
    {
    }
}