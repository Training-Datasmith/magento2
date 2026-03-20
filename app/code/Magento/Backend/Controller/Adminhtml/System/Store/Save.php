<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Forward_Factory;
use Magento\Framework\App\Action\Http_Post_Action_Interface as HttpPostActionInterface;
use Magento\Framework\App\Cache\Type_List_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Filter\Filter_Manager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page_Factory;
use Magento\Store\Model\Group as StoreGroup;
use Magento\Store\Model\Store;
/**
 * Class Save
 *
 * Save controller for system entities such as: Store, StoreGroup, Website
 */
class Save extends \Magento\Backend\Controller\Adminhtml\System\Store implements Http_Post_Action_Interface
{
    /**
     * @var TypeListInterface
     */
    private $cache_type_list;
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FilterManager $filterManager
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(Context $context, Registry $core_registry, Filter_Manager $filter_manager, Forward_Factory $result_forward_factory, Page_Factory $result_page_factory, Type_List_Interface $cache_type_list)
    {
        parent::__construct($context, $core_registry, $filter_manager, $result_forward_factory, $result_page_factory);
        $this->cache_type_list = $cache_type_list;
    }
    /**
     * Process Website model save
     *
     * @param array $postData
     * @return array
     */
    private function process_website_save($post_data)
    {
        $post_data['website']['name'] = $this->filter_manager->remove_tags($post_data['website']['name']);
        $website_model = $this->_object_manager->create(\Magento\Store\Model\Website::class);
        if ($post_data['website']['website_id']) {
            $website_model->load($post_data['website']['website_id']);
        }
        $website_model->set_data($post_data['website']);
        if ($post_data['website']['website_id'] == '') {
            $website_model->set_id(null);
        }
        $group_model = $this->_object_manager->create(Store_Group::class);
        $group_model->load($website_model->get_default_group_id());
        $store_model = $this->_object_manager->create(Store::class);
        $store_model->load($group_model->get_default_store_id());
        if ($website_model->get_is_default() && !$store_model->is_active()) {
            throw new Localized_Exception(__('Please enable your Store View before using this Web Site as Default'));
        }
        $website_model->save();
        $this->message_manager->add_success_message(__('You saved the website.'));
        return $post_data;
    }
    /**
     * Process Store model save
     *
     * @param array $postData
     * @throws LocalizedException
     * @return array
     */
    private function process_store_save($post_data)
    {
        /** @var Store $storeModel */
        $store_model = $this->_object_manager->create(Store::class);
        $post_data['store']['name'] = $this->filter_manager->remove_tags($post_data['store']['name']);
        if ($post_data['store']['store_id']) {
            $store_model->load($post_data['store']['store_id']);
        }
        $original_code = $store_model->get_code();
        $new_code = $post_data['store']['code'] ?? null;
        $store_model->set_data($post_data['store']);
        if ($post_data['store']['store_id'] == '') {
            $store_model->set_id(null);
        }
        $group_model = $this->_object_manager->create(Store_Group::class)->load($store_model->get_group_id());
        $store_model->set_website_id($group_model->get_website_id());
        if (!$store_model->is_active() && $store_model->is_default()) {
            throw new Localized_Exception(__('The default store cannot be disabled'));
        }
        $store_model->save();
        $this->message_manager->add_success_message(__('You saved the store view.'));
        if ($original_code !== $new_code) {
            $this->cache_type_list->clean_type('config');
        }
        return $post_data;
    }
    /**
     * Process StoreGroup model save
     *
     * @param array $postData
     * @throws LocalizedException
     * @return array
     */
    private function process_group_save($post_data)
    {
        $post_data['group']['name'] = $this->filter_manager->remove_tags($post_data['group']['name']);
        /** @var StoreGroup $groupModel */
        $group_model = $this->_object_manager->create(Store_Group::class);
        if ($post_data['group']['group_id']) {
            $group_model->load($post_data['group']['group_id']);
        }
        $group_model->set_data($post_data['group']);
        if ($post_data['group']['group_id'] == '') {
            $group_model->set_id(null);
        }
        if (!$this->is_selected_default_store_active($post_data, $group_model)) {
            throw new Localized_Exception(__('An inactive store view cannot be saved as default store view'));
        }
        $group_model->save();
        $this->message_manager->add_success_message(__('You saved the store.'));
        return $post_data;
    }
    /**
     * Saving edited store information
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $redirectResult */
        $redirect_result = $this->result_redirect_factory->create();
        if ($this->get_request()->is_post() && $post_data = $this->get_request()->get_post_value()) {
            if (empty($post_data['store_type']) || empty($post_data['store_action'])) {
                $redirect_result->set_path('adminhtml/*/');
                return $redirect_result;
            }
            try {
                switch ($post_data['store_type']) {
                    case 'website':
                        $post_data = $this->process_website_save($post_data);
                        break;
                    case 'group':
                        $post_data = $this->process_group_save($post_data);
                        break;
                    case 'store':
                        $post_data = $this->process_store_save($post_data);
                        break;
                    default:
                        $redirect_result->set_path('adminhtml/*/');
                        return $redirect_result;
                }
                $redirect_result->set_path('adminhtml/*/');
                return $redirect_result;
            } catch (Localized_Exception $e) {
                $this->message_manager->add_error_message($e->get_message());
                $this->_get_session()->set_post_data($post_data);
            } catch (\Exception $e) {
                $this->message_manager->add_exception_message($e, __('Something went wrong while saving. Please review the error log.'));
                $this->_get_session()->set_post_data($post_data);
            }
            $redirect_result->set_url($this->_redirect->get_redirect_url($this->get_url('*')));
            return $redirect_result;
        }
        $redirect_result->set_path('adminhtml/*/');
        return $redirect_result;
    }
    /**
     * Verify if selected default store is active
     *
     * @param array $postData
     * @param StoreGroup $groupModel
     * @return bool
     */
    private function is_selected_default_store_active(array $post_data, Store_Group $group_model)
    {
        if (!empty($post_data['group']['default_store_id'])) {
            $default_store_id = $post_data['group']['default_store_id'];
            if (!empty($group_model->get_stores()[$default_store_id]) && !$group_model->get_stores()[$default_store_id]->is_active()) {
                return false;
            }
        }
        return true;
    }
}