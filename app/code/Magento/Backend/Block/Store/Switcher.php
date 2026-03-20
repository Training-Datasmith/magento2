<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Store;

use Magento\Framework\Exception\Localized_Exception;
/**
 * Store switcher block
 *
 * @api
 * @since 100.0.2
 */
class Switcher extends \Magento\Backend\Block\Template
{
    /**
     * URL for store switcher hint
     */
    public const HINT_URL = 'https://experienceleague.adobe.com/docs/commerce-admin/start/setup/websites-stores-views.html#scope-settings';
    // @codingStandardsIgnoreLine
    /**
     * Name of website variable
     *
     * @var string
     */
    protected $_default_website_var_name = 'website';
    /**
     * Name of store group variable
     *
     * @var string
     */
    protected $_default_store_group_var_name = 'group';
    /**
     * Name of store variable
     *
     * @var string
     */
    protected $_default_store_var_name = 'store';
    /**
     * @var array
     */
    protected $_store_ids;
    /**
     * Url for store switcher hint
     *
     * @var string
     */
    protected $_hint_url;
    /**
     * @var bool
     */
    protected $_has_default_option = true;
    /**
     * Block template filename
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::store/switcher.phtml';
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_website_factory;
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_store_group_factory;
    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_store_factory;
    /**
     * Switcher constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Store\Model\GroupFactory $storeGroupFactory
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Store\Model\Website_Factory $website_factory, \Magento\Store\Model\Group_Factory $store_group_factory, \Magento\Store\Model\Store_Factory $store_factory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->_website_factory = $website_factory;
        $this->_store_group_factory = $store_group_factory;
        $this->_store_factory = $store_factory;
    }
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_use_confirm($this->has_data('use_confirm') ? (bool) $this->get_data('use_confirm') : true);
        $this->set_use_ajax(true);
        $this->set_show_manage_stores_link(0);
        if (!$this->has_data('switch_websites')) {
            $this->set_switch_websites(false);
        }
        if (!$this->has_data('switch_store_groups')) {
            $this->set_switch_store_groups(false);
        }
        if (!$this->has_data('switch_store_views')) {
            $this->set_switch_store_views(true);
        }
        $this->set_default_selection_name(__('All Store Views'));
    }
    /**
     * Get website collection.
     *
     * @return \Magento\Store\Model\ResourceModel\Website\Collection
     */
    public function get_website_collection()
    {
        $collection = $this->_website_factory->create()->get_resource_collection();
        $website_ids = $this->get_website_ids();
        if ($website_ids !== null) {
            $collection->add_id_filter($this->get_website_ids());
        }
        return $collection->load();
    }
    /**
     * Get websites
     *
     * @return \Magento\Store\Model\Website[]
     */
    public function get_websites()
    {
        $websites = $this->_store_manager->get_websites();
        if ($website_ids = $this->get_website_ids()) {
            $websites = array_intersect_key($websites, array_flip($website_ids));
        }
        return $websites;
    }
    /**
     * Check if can switch to websites
     *
     * @return bool
     */
    public function is_website_switch_enabled()
    {
        return (bool) $this->get_data('switch_websites');
    }
    /**
     * Set website variable name.
     *
     * @param string $varName
     * @return $this
     */
    public function set_website_var_name($var_name)
    {
        $this->set_data('website_var_name', $var_name);
        return $this;
    }
    /**
     * Get website variable name.
     *
     * @return string
     */
    public function get_website_var_name()
    {
        if ($this->has_data('website_var_name')) {
            return (string) $this->get_data('website_var_name');
        } else {
            return (string) $this->_default_website_var_name;
        }
    }
    /**
     * Check if current website selected.
     *
     * @param \Magento\Store\Model\Website $website
     * @return bool
     */
    public function is_website_selected(\Magento\Store\Model\Website $website)
    {
        return $this->get_website_id() === $website->get_id() && $this->get_store_id() === null;
    }
    /**
     * Return website Id.
     *
     * @return int|null
     */
    public function get_website_id()
    {
        if (!$this->has_data('website_id')) {
            $this->set_data('website_id', (int) $this->get_request()->get_param($this->get_website_var_name()));
        }
        return $this->get_data('website_id');
    }
    /**
     * Return group collection provided website.
     *
     * @param int|\Magento\Store\Model\Website $website
     * @return \Magento\Store\Model\ResourceModel\Group\Collection
     */
    public function get_group_collection($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_website_factory->create()->load($website);
        }
        return $website->get_group_collection();
    }
    /**
     * Get store groups for specified website
     *
     * @param \Magento\Store\Model\Website|int $website
     * @return array
     */
    public function get_store_groups($website)
    {
        if (!$website instanceof \Magento\Store\Model\Website) {
            $website = $this->_store_manager->get_website($website);
        }
        return $website->get_groups();
    }
    /**
     * Check if can switch to store group
     *
     * @return bool
     */
    public function is_store_group_switch_enabled()
    {
        return (bool) $this->get_data('switch_store_groups');
    }
    /**
     * Sets store group variable name.
     *
     * @param string $varName
     * @return $this
     */
    public function set_store_group_var_name($var_name)
    {
        $this->set_data('store_group_var_name', $var_name);
        return $this;
    }
    /**
     * Return store group variable name.
     *
     * @return string
     */
    public function get_store_group_var_name()
    {
        if ($this->has_data('store_group_var_name')) {
            return (string) $this->get_data('store_group_var_name');
        } else {
            return (string) $this->_default_store_group_var_name;
        }
    }
    /**
     * Is provided group selected.
     *
     * @param \Magento\Store\Model\Group $group
     * @return bool
     */
    public function is_store_group_selected(\Magento\Store\Model\Group $group)
    {
        return $this->get_store_group_id() === $group->get_id() && $this->get_store_group_id() === null;
    }
    /**
     * Return store group Id.
     *
     * @return int|null
     */
    public function get_store_group_id()
    {
        if (!$this->has_data('store_group_id')) {
            $this->set_data('store_group_id', (int) $this->get_request()->get_param($this->get_store_group_var_name()));
        }
        return $this->get_data('store_group_id');
    }
    /**
     * Return store collection.
     *
     * @param \Magento\Store\Model\Group|int $group
     * @return \Magento\Store\Model\ResourceModel\Store\Collection
     */
    public function get_store_collection($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_store_group_factory->create()->load($group);
        }
        $stores = $group->get_store_collection();
        $_store_ids = $this->get_store_ids();
        if (!empty($_store_ids)) {
            $stores->add_id_filter($_store_ids);
        }
        return $stores;
    }
    /**
     * Get store views for specified store group
     *
     * @param \Magento\Store\Model\Group|int $group
     * @return \Magento\Store\Model\Store[]
     */
    public function get_stores($group)
    {
        if (!$group instanceof \Magento\Store\Model\Group) {
            $group = $this->_store_manager->get_group($group);
        }
        $stores = $group->get_stores();
        if ($store_ids = $this->get_store_ids()) {
            foreach (array_keys($stores) as $store_id) {
                if (!in_array($store_id, $store_ids)) {
                    unset($stores[$store_id]);
                }
            }
        }
        return $stores;
    }
    /**
     * Return store Id.
     *
     * @return int|null
     */
    public function get_store_id()
    {
        if (!$this->has_data('store_id')) {
            $this->set_data('store_id', (int) $this->get_request()->get_param($this->get_store_var_name()));
        }
        return $this->get_data('store_id');
    }
    /**
     * Check is provided store selected.
     *
     * @param \Magento\Store\Model\Store $store
     * @return bool
     */
    public function is_store_selected(\Magento\Store\Model\Store $store)
    {
        return $this->get_store_id() !== null && (int) $this->get_store_id() === (int) $store->get_id();
    }
    /**
     * Check if can switch to store views
     *
     * @return bool
     */
    public function is_store_switch_enabled()
    {
        return (bool) $this->get_data('switch_store_views');
    }
    /**
     * Sets store variable name.
     *
     * @param string $varName
     * @return $this
     */
    public function set_store_var_name($var_name)
    {
        $this->set_data('store_var_name', $var_name);
        return $this;
    }
    /**
     * Return store variable name.
     *
     * @return mixed|string
     */
    public function get_store_var_name()
    {
        if ($this->has_data('store_var_name')) {
            return (string) $this->get_data('store_var_name');
        } else {
            return (string) $this->_default_store_var_name;
        }
    }
    /**
     * Return switch url.
     *
     * @return string
     */
    public function get_switch_url()
    {
        if ($url = $this->get_data('switch_url')) {
            return $url;
        }
        return $this->get_url('*/*/*', ['_current' => true, $this->get_store_var_name() => null, $this->get_store_group_var_name() => null, $this->get_website_var_name() => null]);
    }
    /**
     * Checks if scope selected.
     *
     * @return bool
     */
    public function has_scope_selected()
    {
        return $this->get_store_id() !== null || $this->get_store_group_id() !== null || $this->get_website_id() !== null;
    }
    /**
     * Get current selection name
     *
     * @return string
     */
    public function get_current_selection_name()
    {
        if ($this->get_current_store_name() !== '') {
            return $this->get_current_store_name();
        }
        if ($this->get_current_store_group_name() !== '') {
            return $this->get_current_store_group_name();
        }
        if ($this->get_current_website_name() !== '') {
            return $this->get_current_website_name();
        }
        if (!$this->has_default_option()) {
            $websites = $this->get_websites();
            if (!empty($websites)) {
                $website_array = array_values($websites);
                return $website_array[0]->get_name();
            }
        }
        return $this->get_default_selection_name();
    }
    /**
     * Get current website name
     *
     * @return string
     */
    public function get_current_website_name()
    {
        $website_id = $this->get_website_id();
        if ($website_id !== null) {
            if ($this->has_data('get_data_from_request')) {
                $requested_website = $this->get_request()->get_params('website');
                if (!empty($requested_website) && array_key_exists('website', $requested_website)) {
                    $website_id = $requested_website['website'];
                }
            }
            $website = $this->_website_factory->create();
            $website->load($website_id);
            if ($website->get_id()) {
                return $website->get_name();
            }
        }
        return '';
    }
    /**
     * Get current store group name
     *
     * @return string
     */
    public function get_current_store_group_name()
    {
        if ($this->get_store_group_id() !== null) {
            $group = $this->_store_group_factory->create();
            $group->load($this->get_store_group_id());
            if ($group->get_id()) {
                return $group->get_name();
            }
        }
        return '';
    }
    /**
     * Get current store view name
     *
     * @return string
     * @throws LocalizedException
     */
    public function get_current_store_name()
    {
        $store_id = $this->get_store_id();
        if ($store_id !== null) {
            if ($this->has_data('get_data_from_request')) {
                $requested_store = $this->get_request()->get_params('store');
                if (!empty($requested_store) && array_key_exists('store', $requested_store)) {
                    $store_id = $requested_store['store'];
                }
            }
            $store = $this->_store_factory->create();
            $store->load($store_id);
            if ($store->get_id()) {
                return $store->get_name();
            }
        }
        return '';
    }
    /**
     * Sets store ids.
     *
     * @param array $storeIds
     * @return $this
     */
    public function set_store_ids($store_ids)
    {
        $this->_store_ids = $store_ids;
        return $this;
    }
    /**
     * Return store ids.
     *
     * @return array
     */
    public function get_store_ids()
    {
        return $this->_store_ids;
    }
    /**
     * Check if system is run in the single store mode.
     *
     * @return bool
     */
    public function is_show()
    {
        return !$this->_store_manager->is_single_store_mode();
    }
    /**
     * Render block.
     *
     * @return string
     */
    protected function _to_html()
    {
        if ($this->is_show()) {
            return parent::_to_html();
        }
        return '';
    }
    /**
     * Set/Get whether the switcher should show default option
     *
     * @param bool $hasDefaultOption
     * @return bool
     */
    public function has_default_option($has_default_option = null)
    {
        if (null !== $has_default_option) {
            $this->_has_default_option = $has_default_option;
        }
        return $this->_has_default_option;
    }
    /**
     * Return url for store switcher hint
     *
     * @return string
     */
    public function get_hint_url()
    {
        return self::HINT_URL;
    }
    /**
     * Return store switcher hint html
     *
     * @return string
     */
    public function get_hint_html()
    {
        $html = '';
        $url = $this->get_hint_url();
        if ($url) {
            $html = '<div class="admin__field-tooltip tooltip"><a href="%s" onclick="this.target=\'_blank\'"  title="%s"
            class="admin__field-tooltip-action action-help"><span>%s</span></a></div>';
            $title = $this->escape_html_attr(__('What is this?'));
            $span = $this->escape_html(__('What is this?'));
            $html = sprintf($html, $this->escape_url($url), $title, $span);
        }
        return $html;
    }
    /**
     * Get whether iframe is being used
     *
     * @return bool
     */
    public function is_using_iframe()
    {
        if ($this->has_data('is_using_iframe')) {
            return (bool) $this->get_data('is_using_iframe');
        }
        return false;
    }
}