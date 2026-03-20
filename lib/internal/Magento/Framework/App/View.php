<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

class View implements View_Interface
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;
    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_config_scope;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_event_manager;
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $page;
    /**
     * @var ActionFlag
     */
    protected $_action_flag;
    /**
     * @var ResponseInterface
     */
    protected $_response;
    /**
     * @var RequestInterface
     */
    protected $_request;
    /**
     * @var bool
     */
    protected $_is_layout_loaded = false;
    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param ActionFlag $actionFlag
     */
    public function __construct(\Magento\Framework\View\Layout_Interface $layout, Request_Interface $request, Response_Interface $response, \Magento\Framework\Config\Scope_Interface $config_scope, \Magento\Framework\Event\Manager_Interface $event_manager, \Magento\Framework\View\Result\Page_Factory $page_factory, Action_Flag $action_flag)
    {
        $this->_layout = $layout;
        $this->_request = $request;
        $this->_response = $response;
        $this->_config_scope = $config_scope;
        $this->_event_manager = $event_manager;
        $this->_action_flag = $action_flag;
        $this->page = $page_factory->create(true);
    }
    /**
     * Retrieve current page object
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function get_page()
    {
        return $this->page;
    }
    /**
     * Retrieve current layout object
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function get_layout()
    {
        return $this->page->get_layout();
    }
    /**
     * {@inheritdoc}
     */
    public function load_layout($handles = null, $generate_blocks = true, $generate_xml = true, $add_action_handles = true)
    {
        if ($this->_is_layout_loaded) {
            throw new \RuntimeException('Layout must be loaded only once.');
        }
        // if handles were specified in arguments load them first
        if (!empty($handles)) {
            $this->get_layout()->get_update()->add_handle($handles);
        }
        if ($add_action_handles) {
            // add default layout handles for this action
            $this->page->init_layout();
        }
        $this->load_layout_updates();
        if (!$generate_xml) {
            return $this;
        }
        $this->generate_layout_xml();
        if (!$generate_blocks) {
            return $this;
        }
        $this->generate_layout_blocks();
        $this->_is_layout_loaded = true;
        return $this;
    }
    /**
     * Retrieve the default layout handle name for the current action
     *
     * @return string
     */
    public function get_default_layout_handle()
    {
        return $this->page->get_default_layout_handle();
    }
    /**
     * Add layout handle by full controller action name
     *
     * @return $this
     */
    public function add_action_layout_handles()
    {
        $this->get_layout()->get_update()->add_handle($this->get_default_layout_handle());
        return $this;
    }
    /**
     * Add layout updates handles associated with the action page
     *
     * @param array|null $parameters page parameters
     * @param string|null $defaultHandle
     * @return bool
     */
    public function add_page_layout_handles(array $parameters = [], $default_handle = null)
    {
        return $this->page->add_page_layout_handles($parameters, $default_handle);
    }
    /**
     * Load layout updates
     *
     * @return $this
     */
    public function load_layout_updates()
    {
        $this->page->get_config()->public_build();
        return $this;
    }
    /**
     * Generate layout xml
     *
     * @return $this
     */
    public function generate_layout_xml()
    {
        $this->page->get_config()->public_build();
        return $this;
    }
    /**
     * Generate layout blocks
     *
     * @return $this
     */
    public function generate_layout_blocks()
    {
        $this->page->get_config()->public_build();
        return $this;
    }
    /**
     * Rendering layout
     *
     * @param   string $output
     * @return  $this
     */
    public function render_layout($output = '')
    {
        if ($this->_action_flag->get('', 'no-renderLayout')) {
            return $this;
        }
        \Magento\Framework\Profiler::start('LAYOUT');
        \Magento\Framework\Profiler::start('layout_render');
        if ('' !== $output) {
            $this->get_layout()->add_output_element($output);
        }
        $this->_event_manager->dispatch('controller_action_layout_render_before');
        $this->_event_manager->dispatch('controller_action_layout_render_before_' . $this->_request->get_full_action_name());
        $this->page->render_result($this->_response);
        \Magento\Framework\Profiler::stop('layout_render');
        \Magento\Framework\Profiler::stop('LAYOUT');
        return $this;
    }
    /**
     * Set isLayoutLoaded flag
     *
     * @param bool $value
     * @return void
     */
    public function set_is_layout_loaded($value)
    {
        $this->_is_layout_loaded = $value;
    }
    /**
     * Returns is layout loaded
     *
     * @return bool
     */
    public function is_layout_loaded()
    {
        return $this->_is_layout_loaded;
    }
}