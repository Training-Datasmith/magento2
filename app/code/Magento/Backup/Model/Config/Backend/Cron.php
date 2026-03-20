<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backup\Model\Config\Backend;

/**
 * Backup by cron backend model
 * @api
 * @since 100.0.2
 */
class Cron extends \Magento\Framework\App\Config\Value
{
    public const CRON_STRING_PATH = 'crontab/default/jobs/system_backup/schedule/cron_expr';
    public const CRON_MODEL_PATH = 'crontab/default/jobs/system_backup/run/model';
    public const XML_PATH_BACKUP_ENABLED = 'groups/backup/fields/enabled/value';
    public const XML_PATH_BACKUP_TIME = 'groups/backup/fields/time/value';
    public const XML_PATH_BACKUP_FREQUENCY = 'groups/backup/fields/frequency/value';
    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_config_value_factory;
    /**
     * @var string
     */
    protected $_run_model_path = '';
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\Scope_Config_Interface $config, \Magento\Framework\App\Cache\Type_List_Interface $cache_type_list, \Magento\Framework\App\Config\Value_Factory $config_value_factory, ?\Magento\Framework\Model\Resource_Model\Abstract_Resource $resource = null, ?\Magento\Framework\Data\Collection\Abstract_Db $resource_collection = null, $run_model_path = '', array $data = [])
    {
        $this->_run_model_path = $run_model_path;
        $this->_config_value_factory = $config_value_factory;
        parent::__construct($context, $registry, $config, $cache_type_list, $resource, $resource_collection, $data);
    }
    /**
     * Cron settings after save
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function after_save()
    {
        $enabled = $this->get_data(self::XML_PATH_BACKUP_ENABLED);
        $time = $this->get_data(self::XML_PATH_BACKUP_TIME);
        $frequency = $this->get_data(self::XML_PATH_BACKUP_FREQUENCY);
        $frequency_weekly = \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY;
        $frequency_monthly = \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY;
        if ($enabled) {
            $cron_expr_array = [
                (int) $time[1],
                # Minute
                (int) $time[0],
                # Hour
                $frequency == $frequency_monthly ? '1' : '*',
                # Day of the Month
                '*',
                # Month of the Year
                $frequency == $frequency_weekly ? '1' : '*',
            ];
            $cron_expr_string = join(' ', $cron_expr_array);
        } else {
            $cron_expr_string = '';
        }
        try {
            $this->_config_value_factory->create()->load(self::CRON_STRING_PATH, 'path')->set_value($cron_expr_string)->set_path(self::CRON_STRING_PATH)->save();
            $this->_config_value_factory->create()->load(self::CRON_MODEL_PATH, 'path')->set_value($this->_run_model_path)->set_path(self::CRON_MODEL_PATH)->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('We can\'t save the Cron expression.'));
        }
        return parent::after_save();
    }
}