<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model\Resource_Model;

use Magento\Backend\App\Abstract_Action;
use Magento\Framework\Acl\Data\Cache_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Model\Resource_Model\Db\Abstract_Db;
use Magento\Framework\Model\Resource_Model\Db\Context;
use Psr\Log\Logger_Interface;
/**
 * Admin rule resource model
 */
class Rules extends Abstract_Db
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;
    /**
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        protected \Magento\Framework\Acl\Builder $_acl_builder,
        Logger_Interface $logger,
        /**
         * Root ACL resource
         */
        protected \Magento\Framework\Acl\Root_Resource $_root_resource,
        private readonly Cache_Interface $acl_data_cache,
        $connection_name = null
    )
    {
        parent::__construct($context, $connection_name);
        $this->_logger = $logger;
    }
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('authorization_rule', 'rule_id');
    }
    /**
     * Save ACL resources
     *
     * @throws LocalizedException
     */
    public function save_rel(\Magento\Authorization\Model\Rules $rule): void
    {
        $connection = $this->get_connection();
        try {
            $connection->begin_transaction();
            $role_id = $rule->get_role_id();
            $condition = ['role_id = ?' => (int) $role_id];
            $connection->delete($this->get_main_table(), $condition);
            $posted_resources = $rule->get_resources();
            if ($posted_resources) {
                $row = [
                    'resource_id' => $this->_root_resource->get_id(),
                    'privileges' => '',
                    // not used yet
                    'role_id' => $role_id,
                    'permission' => 'allow',
                ];
                // If all was selected save it only and nothing else.
                if ($posted_resources === [$this->_root_resource->get_id()]) {
                    $insert_data = $this->_prepare_data_for_table(new \Magento\Framework\Data_Object($row), $this->get_main_table());
                    $connection->insert($this->get_main_table(), $insert_data);
                } else {
                    /** Give basic admin permissions to any admin */
                    $posted_resources[] = Abstract_Action::ADMIN_RESOURCE;
                    $acl = $this->_acl_builder->get_acl();
                    /** @var $resource \Magento\Framework\Acl\AclResource */
                    foreach ($acl->get_resources() as $resource_id) {
                        $row['permission'] = in_array($resource_id, $posted_resources) ? 'allow' : 'deny';
                        $row['resource_id'] = $resource_id;
                        $insert_data = $this->_prepare_data_for_table(new \Magento\Framework\Data_Object($row), $this->get_main_table());
                        $connection->insert($this->get_main_table(), $insert_data);
                    }
                }
            }
            $connection->commit();
            $this->acl_data_cache->clean();
        } catch (Localized_Exception $e) {
            $connection->roll_back();
            throw $e;
        } catch (\Exception $e) {
            $connection->roll_back();
            $this->_logger->critical($e);
        }
    }
}