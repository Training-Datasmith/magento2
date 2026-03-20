<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Index;

use Magento\Backend\Controller\Adminhtml\Index as IndexAction;
use Magento\Framework\App\Action\Http_Get_Action_Interface;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPostActionInterface;
/**
 * @api
 * @since 100.0.2
 */
class Global_Search extends Index_Action implements Http_Get_Action_Interface, Http_Post_Action_Interface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $result_json_factory;
    /**
     * Search modules list
     *
     * @var array
     */
    protected $_search_modules;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param array $searchModules
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Controller\Result\Json_Factory $result_json_factory, array $search_modules = [])
    {
        $this->_search_modules = $search_modules;
        parent::__construct($context);
        $this->result_json_factory = $result_json_factory;
    }
    /**
     * Global Search Action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $items = [];
        if (!$this->_authorization->is_allowed('Magento_Backend::global_search')) {
            $items[] = ['id' => 'error', 'type' => __('Error'), 'name' => __('Access Denied.'), 'description' => __('You need more permissions to do this.')];
        } else if (empty($this->_search_modules)) {
            $items[] = ['id' => 'error', 'type' => __('Error'), 'name' => __('No search modules were registered'), 'description' => __('Please make sure that all global admin search modules are installed and activated.')];
        } else {
            $start = $this->get_request()->get_param('start', 1);
            $limit = $this->get_request()->get_param('limit', 10);
            $query = $this->get_request()->get_param('query', '');
            foreach ($this->_search_modules as $search_config) {
                if ($search_config['acl'] && !$this->_authorization->is_allowed($search_config['acl'])) {
                    continue;
                }
                $class_name = $search_config['class'];
                if (empty($class_name)) {
                    continue;
                }
                $search_instance = $this->_object_manager->create($class_name);
                $results = $search_instance->set_start($start)->set_limit($limit)->set_query($query)->load()->get_results();
                $items = array_merge_recursive($items, $results);
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $result_json = $this->result_json_factory->create();
        return $result_json->set_data($items);
    }
}