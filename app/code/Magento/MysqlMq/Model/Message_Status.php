<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\MysqlMq\Model;

/**
 * Message model for message status
 *
 * @api
 * @since 100.0.2
 */
class MessageStatus extends \Magento\Framework\Model\AbstractModel
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magento\MysqlMq\Model\ResourceModel\MessageStatus::class);
    }
}
