<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Json\Helper\Data as JsonHelper;
/**
 * Standard admin block. Adds admin-specific behavior and event.
 * Should be used when you declare a block in admin layout handle.
 *
 * Avoid extending this class if possible.
 *
 * If you need custom presentation logic in your blocks, use this class as block, and declare
 * custom view models in block arguments in layout handle file.
 *
 * Example:
 * <block name="my.block" class="Magento\Backend\Block\Template" template="My_Module::template.phtml" >
 *      <arguments>
 *          <argument name="view_model" xsi:type="object">My\Module\ViewModel\Custom</argument>
 *      </arguments>
 * </block>
 *
 * Your class object can then be accessed by doing $block->getViewModel()
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @since 100.0.2
 */
class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $math_random;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backend_session;
    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $form_key;
    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $name_builder;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, array $data = [], ?Json_Helper $json_helper = null, ?Directory_Helper $directory_helper = null)
    {
        $this->_locale_date = $context->get_locale_date();
        $this->_authorization = $context->get_authorization();
        $this->math_random = $context->get_math_random();
        $this->_backend_session = $context->get_backend_session();
        $this->form_key = $context->get_form_key();
        $this->name_builder = $context->get_name_builder();
        $data['jsonHelper'] = $json_helper ?? Object_Manager::get_instance()->get(Json_Helper::class);
        if (empty($data['directoryHelper'])) {
            $data['directoryHelper'] = $directory_helper ?? Object_Manager::get_instance()->get(Directory_Helper::class);
        }
        parent::__construct($context, $data);
    }
    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function get_form_key()
    {
        return $this->form_key->get_form_key();
    }
    /**
     * Check whether or not the module output is enabled.
     *
     * Because many module blocks belong to Backend module,
     * the feature "Disable module output" doesn't cover Admin area.
     *
     * @param string $moduleName Full module name
     * @return boolean
     * @deprecated 100.2.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
     * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
     * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
     * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
     * issues that will be addressed in future releases.
     * @see no alternatives
     */
    public function is_output_enabled($module_name = null)
    {
        if ($module_name === null) {
            $module_name = $this->get_module_name();
        }
        return !$this->_scope_config->is_set_flag('advanced/modules_disable_output/' . $module_name, \Magento\Store\Model\Scope_Interface::SCOPE_STORE);
    }
    /**
     * Make this public so that templates can use it properly with template engine
     *
     * @return \Magento\Framework\AuthorizationInterface
     */
    public function get_authorization()
    {
        return $this->_authorization;
    }
    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _to_html()
    {
        $this->_event_manager->dispatch('adminhtml_block_html_before', ['block' => $this]);
        return parent::_to_html();
    }
    /**
     * Return toolbar block instance
     *
     * @return bool|\Magento\Framework\View\Element\BlockInterface
     */
    public function get_toolbar()
    {
        return $this->get_layout()->get_block('page.actions.toolbar');
    }
}