<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Cron;

/**
 * Captcha cron actions
 */
class Delete_Old_Attempts
{
    /**
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     */
    protected $res_log_factory;
    /**
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     */
    public function __construct(\Magento\Captcha\Model\Resource_Model\Log_Factory $res_log_factory)
    {
        $this->res_log_factory = $res_log_factory;
    }
    /**
     * Delete Unnecessary logged attempts
     *
     * @return \Magento\Captcha\Cron\DeleteOldAttempts
     */
    public function execute()
    {
        $this->res_log_factory->create()->delete_old_attempts();
        return $this;
    }
}