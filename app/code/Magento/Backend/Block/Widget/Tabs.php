<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Widget\Tab\Tab_Interface;
/**
 * Tabs widget
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Tabs extends \Magento\Backend\Block\Widget
{
    /**
     * Tabs structure
     *
     * @var array
     */
    protected $_tabs = [];
    /**
     * Active tab key
     *
     * @var string
     */
    protected $_active_tab = null;
    /**
     * Destination HTML element id
     *
     * @var string
     */
    protected $_dest_element_id = 'content';
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabs.phtml';
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_auth_session;
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $_json_encoder;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\Json\Encoder_Interface $json_encoder, \Magento\Backend\Model\Auth\Session $auth_session, array $data = [])
    {
        $this->_auth_session = $auth_session;
        parent::__construct($context, $data);
        $this->_json_encoder = $json_encoder;
    }
    /**
     * Retrieve destination html element id
     *
     * @return string
     */
    public function get_dest_element_id()
    {
        return $this->_dest_element_id;
    }
    /**
     * Set destination element id
     *
     * @param string $elementId
     * @return $this
     */
    public function set_dest_element_id($element_id)
    {
        $this->_dest_element_id = $element_id;
        return $this;
    }
    /**
     * Add new tab after another
     *
     * @param   string $tabId new tab Id
     * @param   array|\Magento\Framework\DataObject $tab
     * @param   string $afterTabId
     * @return  void
     */
    public function add_tab_after($tab_id, $tab, $after_tab_id)
    {
        $this->add_tab($tab_id, $tab);
        $this->_tabs[$tab_id]->set_after($after_tab_id);
    }
    /**
     * Add new tab
     *
     * @param   string $tabId
     * @param   array|\Magento\Framework\DataObject|string $tab
     * @return  $this
     * @throws  \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function add_tab($tab_id, $tab)
    {
        if (empty($tab_id)) {
            throw new \Exception(__('Please correct the tab configuration and try again. Tab Id should be not empty'));
        }
        if (is_array($tab)) {
            $this->_tabs[$tab_id] = new \Magento\Framework\Data_Object($tab);
        } elseif ($tab instanceof \Magento\Framework\Data_Object) {
            $this->_tabs[$tab_id] = $tab;
            if (!$this->_tabs[$tab_id]->has_tab_id()) {
                $this->_tabs[$tab_id]->set_tab_id($tab_id);
            }
        } elseif (is_string($tab)) {
            $this->_add_tab_by_name($tab, $tab_id);
            if (!$this->_tabs[$tab_id] instanceof Tab_Interface) {
                unset($this->_tabs[$tab_id]);
                return $this;
            }
        } else {
            throw new \Exception(__('Please correct the tab configuration and try again.'));
        }
        if ($this->_tabs[$tab_id]->get_url() === null) {
            $this->_tabs[$tab_id]->set_url('#');
        }
        if (!$this->_tabs[$tab_id]->get_title()) {
            $this->_tabs[$tab_id]->set_title($this->_tabs[$tab_id]->get_label());
        }
        $this->_tabs[$tab_id]->set_id($tab_id);
        $this->_tabs[$tab_id]->set_tab_id($tab_id);
        if (true === $this->_tabs[$tab_id]->get_active()) {
            $this->set_active_tab($tab_id);
        }
        return $this;
    }
    /**
     * Add tab by tab block name
     *
     * @param string $tab
     * @param string $tabId
     * @return void
     * @throws \Exception
     */
    protected function _add_tab_by_name($tab, $tab_id)
    {
        if (strpos($tab, '\Block\\') !== false) {
            $this->_tabs[$tab_id] = $this->get_layout()->create_block($tab, $this->get_name_in_layout() . '_tab_' . $tab_id);
        } elseif ($this->get_child_block($tab)) {
            $this->_tabs[$tab_id] = $this->get_child_block($tab);
        } else {
            $this->_tabs[$tab_id] = null;
        }
        if ($this->_tabs[$tab_id] !== null && !$this->_tabs[$tab_id] instanceof Tab_Interface) {
            throw new \Exception(__('Please correct the tab configuration and try again.'));
        }
    }
    /**
     * Get active tab id
     *
     * @return string
     */
    public function get_active_tab_id()
    {
        return $this->get_tab_id($this->_tabs[$this->_active_tab]);
    }
    /**
     * Set Active Tab
     *
     * Tab has to be not hidden and can show
     *
     * @param string $tabId
     * @return $this
     */
    public function set_active_tab($tab_id)
    {
        if (isset($this->_tabs[$tab_id]) && $this->can_show_tab($this->_tabs[$tab_id]) && !$this->get_tab_is_hidden($this->_tabs[$tab_id])) {
            $this->_active_tab = $tab_id;
            if ($this->_active_tab !== null && $tab_id !== $this->_active_tab) {
                foreach ($this->_tabs as $id => $tab) {
                    $tab->set_active($id === $tab_id);
                }
            }
        }
        return $this;
    }
    /**
     * Set Active Tab
     *
     * @param string $tabId
     * @return $this
     */
    protected function _set_active_tab($tab_id)
    {
        foreach ($this->_tabs as $id => $tab) {
            if ($this->get_tab_id($tab) == $tab_id) {
                $this->_active_tab = $id;
                $tab->set_active(true);
                return $this;
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     */
    protected function _before_to_html()
    {
        $this->_tabs = $this->reorder_tabs();
        if ($active_tab = $this->get_request()->get_param('active_tab')) {
            $this->set_active_tab($active_tab);
        } elseif ($active_tab_id = $this->_auth_session->get_active_tab_id()) {
            $this->_set_active_tab($active_tab_id);
        }
        if ($this->_active_tab === null && !empty($this->_tabs)) {
            /** @var TabInterface $tab */
            $this->_active_tab = reset($this->_tabs)->get_id();
        }
        $this->assign('tabs', $this->_tabs);
        return parent::_before_to_html();
    }
    /**
     * Reorder the tabs.
     *
     * @return array
     */
    private function reorder_tabs()
    {
        $order_by_identity = [];
        $order_by_position = [];
        $position = 100;
        /**
         * Set the initial positions for each tab.
         *
         * @var string       $key
         * @var TabInterface $tab
         */
        foreach ($this->_tabs as $key => $tab) {
            $tab->set_position($position);
            $order_by_identity[$key] = $tab;
            $order_by_position[$position] = $tab;
            $position += 100;
        }
        return $this->apply_tabs_correct_order($order_by_position, $order_by_identity);
    }
    /**
     * Apply tabs order
     *
     * @param array $orderByPosition
     * @param array $orderByIdentity
     *
     * @return array
     */
    private function apply_tabs_correct_order(array $order_by_position, array $order_by_identity)
    {
        $position_factor = 1;
        /**
         * Rearrange the positions by using the after tag for each tab.
         *
         * @var int $position
         * @var TabInterface $tab
         */
        foreach ($order_by_position as $position => $tab) {
            if (!$tab->get_after() || !in_array($tab->get_after(), array_keys($order_by_identity))) {
                $position_factor = 1;
                continue;
            }
            $grand_position = $order_by_identity[$tab->get_after()]->get_position();
            $new_position = $grand_position + $position_factor;
            unset($order_by_position[$position]);
            $order_by_position[$new_position] = $tab;
            $tab->set_position($new_position);
            $position_factor++;
        }
        return $this->final_tabs_sort_order($order_by_position);
    }
    /**
     * Apply the last sort order to tabs.
     *
     * @param array $orderByPosition
     *
     * @return array
     */
    private function final_tabs_sort_order(array $order_by_position)
    {
        ksort($order_by_position);
        $ordered = [];
        /** @var TabInterface $tab */
        foreach ($order_by_position as $tab) {
            $ordered[$tab->get_id()] = $tab;
        }
        return $ordered;
    }
    /**
     * Get js object name
     *
     * @return string
     */
    public function get_js_object_name()
    {
        return $this->get_id() . 'JsTabs';
    }
    /**
     * Get tabs ids
     *
     * @return string[]
     */
    public function get_tabs_ids()
    {
        if (empty($this->_tabs)) {
            return [];
        }
        return array_keys($this->_tabs);
    }
    /**
     * Get tab id
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @param bool $withPrefix
     * @return string
     */
    public function get_tab_id($tab, $with_prefix = true)
    {
        if ($tab instanceof Tab_Interface) {
            return ($with_prefix ? $this->get_id() . '_' : '') . $tab->get_tab_id();
        }
        return ($with_prefix ? $this->get_id() . '_' : '') . $tab->get_id();
    }
    /**
     * CVan show tab
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return bool
     */
    public function can_show_tab($tab)
    {
        if ($tab instanceof Tab_Interface) {
            return $tab->can_show_tab();
        }
        return true;
    }
    /**
     * Get tab is hidden
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_tab_is_hidden($tab)
    {
        if ($tab instanceof Tab_Interface) {
            return $tab->is_hidden();
        }
        return $tab->get_is_hidden();
    }
    /**
     * Get tab url
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return string
     */
    public function get_tab_url($tab)
    {
        if ($tab instanceof Tab_Interface) {
            if (method_exists($tab, 'getTabUrl')) {
                return $tab->get_tab_url();
            }
            return '#';
        }
        if ($tab->get_url() !== null) {
            return $tab->get_url();
        }
        return '#';
    }
    /**
     * Get tab title
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return string
     */
    public function get_tab_title($tab)
    {
        if ($tab instanceof Tab_Interface) {
            return $tab->get_tab_title();
        }
        return $tab->get_title();
    }
    /**
     * Get tab class
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return string
     */
    public function get_tab_class($tab)
    {
        if ($tab instanceof Tab_Interface) {
            if (method_exists($tab, 'getTabClass')) {
                return $tab->get_tab_class();
            }
            return '';
        }
        return $tab->get_class();
    }
    /**
     * Get tab label
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return string
     */
    public function get_tab_label($tab)
    {
        if ($tab instanceof Tab_Interface) {
            return $tab->get_tab_label();
        }
        return $tab->get_label();
    }
    /**
     * Get tab content
     *
     * @param \Magento\Framework\DataObject|TabInterface $tab
     * @return string
     */
    public function get_tab_content($tab)
    {
        if ($tab instanceof Tab_Interface) {
            if ($tab->get_skip_generate_content()) {
                return '';
            }
            return $tab->to_html();
        }
        return $tab->get_content();
    }
    /**
     * Mark tabs as dependent of each other
     *
     * Arbitrary number of tabs can be specified, but at least two
     *
     * @param string $tabOneId
     * @param string $tabTwoId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function bind_shadow_tabs($tab_one_id, $tab_two_id)
    {
        $tabs = [];
        $args = func_get_args();
        if (!empty($args) && count($args) > 1) {
            foreach ($args as $tab_id) {
                if (isset($this->_tabs[$tab_id])) {
                    $tabs[$tab_id] = $tab_id;
                }
            }
            $block_id = $this->get_id();
            foreach ($tabs as $tab_id) {
                foreach ($tabs as $tab_to_id) {
                    if ($tab_id !== $tab_to_id) {
                        if (!$this->_tabs[$tab_to_id]->get_data('shadow_tabs')) {
                            $this->_tabs[$tab_to_id]->set_data('shadow_tabs', []);
                        }
                        $this->_tabs[$tab_to_id]->set_data('shadow_tabs', array_merge($this->_tabs[$tab_to_id]->get_data('shadow_tabs'), [$block_id . '_' . $tab_id]));
                    }
                }
            }
        }
    }
    /**
     * Obtain shadow tabs information
     *
     * @param bool $asJson
     * @return array|string
     */
    public function get_all_shadow_tabs($as_json = true)
    {
        $result = [];
        if (!empty($this->_tabs)) {
            $block_id = $this->get_id();
            foreach (array_keys($this->_tabs) as $tab_id) {
                if ($this->_tabs[$tab_id]->get_data('shadow_tabs')) {
                    $result[$block_id . '_' . $tab_id] = $this->_tabs[$tab_id]->get_data('shadow_tabs');
                }
            }
        }
        if ($as_json) {
            return $this->_json_encoder->encode($result);
        }
        return $result;
    }
    /**
     * Set tab property by tab's identifier
     *
     * @param string $tab
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set_tab_data($tab, $key, $value)
    {
        if (isset($this->_tabs[$tab]) && $this->_tabs[$tab] instanceof \Magento\Framework\Data_Object) {
            if ($key == 'url') {
                $value = $this->get_url($value, ['_current' => true, '_use_rewrite' => true]);
            }
            $this->_tabs[$tab]->set_data($key, $value);
        }
        return $this;
    }
    /**
     * Removes tab with passed id from tabs block
     *
     * @param string $tabId
     * @return $this
     */
    public function remove_tab($tab_id)
    {
        if (isset($this->_tabs[$tab_id])) {
            unset($this->_tabs[$tab_id]);
        }
        return $this;
    }
}