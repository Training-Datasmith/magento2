<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Config\Backend;

use Magento\Framework\App\Cache\Type_List_Interface;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\App\Config\Storage\Writer_Interface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Magento\Framework\Registry;
/**
 * Config value backend model.
 */
class Collection_Time extends Value
{
    /**
     * The path to config setting of schedule of collection data cron.
     */
    public const CRON_SCHEDULE_PATH = 'crontab/analytics/jobs/analytics_collect_data/schedule/cron_expr';
    public function __construct(Context $context, Registry $registry, Scope_Config_Interface $config, Type_List_Interface $cache_type_list, private readonly Writer_Interface $config_writer, ?Abstract_Resource $resource = null, ?Abstract_Db $resource_collection = null, array $data = [])
    {
        parent::__construct($context, $registry, $config, $cache_type_list, $resource, $resource_collection, $data);
    }
    /**
     * @inheritdoc
     *
     * {@inheritdoc}. Set schedule setting for cron.
     *
     * @return Value
     */
    public function after_save()
    {
        $result = preg_match('#(?<hour>\d{2}),(?<min>\d{2}),(?<sec>\d{2})#', $this->get_value(), $time);
        if (!$result) {
            throw new Localized_Exception(__('The time value is using an unsupported format. Enter a supported format and try again.'));
        }
        $cron_expr_array = [
            $time['min'],
            # Minute
            $time['hour'],
            # Hour
            '*',
            # Day of the Month
            '*',
            # Month of the Year
            '*',
        ];
        $cron_expr_string = join(' ', $cron_expr_array);
        try {
            $this->config_writer->save(self::CRON_SCHEDULE_PATH, $cron_expr_string);
        } catch (\Exception $e) {
            $this->_logger->error($e->get_message());
            throw new Localized_Exception(__('Cron settings can\'t be saved'));
        }
        return parent::after_save();
    }
}