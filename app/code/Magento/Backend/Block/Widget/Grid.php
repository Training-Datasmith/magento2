<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget;

/**
 * Backend grid widget block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @method string getRowClickCallback() getRowClickCallback()
 * @method \Magento\Backend\Block\Widget\Grid setRowClickCallback(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Widget
{
    /**
     * Page and sorting var names
     *
     * @var string
     */
    protected $_var_name_limit = 'limit';
    /**
     * @var string
     */
    protected $_var_name_page = 'page';
    /**
     * @var string
     */
    protected $_var_name_sort = 'sort';
    /**
     * @var string
     */
    protected $_var_name_dir = 'dir';
    /**
     * @var string
     */
    protected $_var_name_filter = 'filter';
    /**
     * @var int
     */
    protected $_default_limit = 20;
    /**
     * @var int
     */
    protected $_default_page = 1;
    /**
     * @var bool|string
     */
    protected $_default_sort = false;
    /**
     * @var string
     */
    protected $_default_dir = 'desc';
    /**
     * @var array
     */
    protected $_default_filter = [];
    /**
     * Empty grid text
     *
     * @var string|null
     */
    protected $_empty_text;
    /**
     * Empty grid text CSS class
     *
     * @var string|null
     */
    protected $_empty_text_css = 'empty-text';
    /**
     * Pager visibility
     *
     * @var boolean
     */
    protected $_pager_visibility = true;
    /**
     * Massage block visibility
     *
     * @var boolean
     */
    protected $_message_block_visibility = false;
    /**
     * Should parameters be saved in session
     *
     * @var bool
     */
    protected $_save_parameters_in_session = false;
    /**
     * Count totals
     *
     * @var boolean
     */
    protected $_count_totals = false;
    /**
     * Totals
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_var_totals;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/grid.phtml';
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backend_session;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_backend_helper;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backend_helper, array $data = [])
    {
        $this->_backend_helper = $backend_helper;
        $this->_backend_session = $context->get_backend_session();
        parent::__construct($context, $data);
    }
    /**
     * Internal constructor, that is called from real constructor
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _construct()
    {
        parent::_construct();
        if (!$this->get_row_click_callback()) {
            $this->set_row_click_callback('openGridRow');
        }
        if ($this->has_data('id')) {
            $this->set_id($this->get_data('id'));
        }
        if ($this->has_data('default_sort')) {
            $this->set_default_sort($this->get_data('default_sort'));
        }
        if ($this->has_data('default_dir')) {
            $this->set_default_dir($this->get_data('default_dir'));
        }
        if ($this->has_data('save_parameters_in_session')) {
            $this->set_save_parameters_in_session($this->get_data('save_parameters_in_session'));
        }
        $this->set_pager_visibility($this->has_data('pager_visibility') ? (bool) $this->get_data('pager_visibility') : true);
        $this->set_data('use_ajax', $this->has_data('use_ajax') ? (bool) $this->get_data('use_ajax') : false);
    }
    /**
     * Set collection object
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return void
     */
    public function set_collection($collection)
    {
        $this->set_data('dataSource', $collection);
    }
    /**
     * Get collection object
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function get_collection()
    {
        return $this->get_data('dataSource');
    }
    /**
     * Retrieve column set block
     *
     * @return \Magento\Backend\Block\Widget\Grid\ColumnSet
     */
    public function get_column_set()
    {
        return $this->get_child_block('grid.columnSet');
    }
    /**
     * Retrieve export block
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function get_export_block()
    {
        if (!$this->get_child_block('grid.export')) {
            throw new \Magento\Framework\Exception\Localized_Exception(__('Export block for grid %1 is not defined', $this->get_name_in_layout()));
        }
        return $this->get_child_block('grid.export');
    }
    /**
     * Retrieve list of grid columns
     *
     * @return array
     */
    public function get_columns()
    {
        return $this->get_column_set()->get_columns();
    }
    /**
     * Count grid columns
     *
     * @return int
     */
    public function get_column_count()
    {
        return count($this->get_columns());
    }
    /**
     * Retrieve column by id
     *
     * @param string $columnId
     * @return \Magento\Framework\View\Element\AbstractBlock|bool
     */
    public function get_column($column_id)
    {
        return $this->get_column_set()->get_child_block($column_id);
    }
    /**
     * Process column filtration values
     *
     * @param mixed $data
     * @return $this
     */
    protected function _set_filter_values($data)
    {
        foreach ($this->get_columns() as $column_id => $column) {
            if (isset($data[$column_id]) && (is_array($data[$column_id]) && !empty($data[$column_id]) || strlen($data[$column_id]) > 0) && $column->get_filter()) {
                $column->get_filter()->set_value($data[$column_id]);
                $this->_add_column_filter_to_collection($column);
            }
        }
        return $this;
    }
    /**
     * Add column filtering conditions to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _add_column_filter_to_collection($column)
    {
        if ($this->get_collection()) {
            $field = $column->get_filter_index() ? $column->get_filter_index() : $column->get_index();
            if ($column->get_filter_condition_callback()) {
                $object = isset($column->get_filter_condition_callback()['object']) ? $column->get_filter_condition_callback()['object'] : $column->get_filter_condition_callback()[0];
                $method = isset($column->get_filter_condition_callback()['method']) ? $column->get_filter_condition_callback()['method'] : $column->get_filter_condition_callback()[1];
                $object->{$method}($this->get_collection(), $column);
            } else {
                $condition = $column->get_filter()->get_condition();
                if ($field && $condition) {
                    $this->get_collection()->add_field_to_filter($field, $condition);
                }
            }
        }
        return $this;
    }
    /**
     * Sets sorting order by some column
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _set_collection_order($column)
    {
        $collection = $this->get_collection();
        if ($collection) {
            $column_index = $column->get_filter_index() ? $column->get_filter_index() : $column->get_index();
            $collection->set_order($column_index, strtoupper($column->get_dir()));
        }
        return $this;
    }
    /**
     * Get prepared collection
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function get_prepared_collection()
    {
        $this->_prepare_collection();
        return $this->get_collection();
    }
    /**
     * Apply sorting and filtering to collection
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepare_collection()
    {
        if ($this->get_collection()) {
            $this->_prepare_page();
            $column_id = $this->get_param($this->get_var_name_sort(), $this->_default_sort);
            $dir = $this->get_param($this->get_var_name_dir(), $this->_default_dir);
            $filter = $this->get_param($this->get_var_name_filter(), null);
            if ($filter === null) {
                $filter = $this->_default_filter;
            }
            if (is_string($filter)) {
                $data = $this->_backend_helper->prepare_filter_string($filter);
                $data = array_merge($data, (array) $this->get_request()->get_post($this->get_var_name_filter()));
                $this->_set_filter_values($data);
            } elseif ($filter && is_array($filter)) {
                $this->_set_filter_values($filter);
            } elseif (0 !== count($this->_default_filter)) {
                $this->_set_filter_values($this->_default_filter);
            }
            if ($this->get_column($column_id) && $this->get_column($column_id)->get_index()) {
                $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
                $this->get_column($column_id)->set_dir($dir);
                $this->_set_collection_order($this->get_column($column_id));
            }
        }
        return $this;
    }
    /**
     * Apply pagination to collection
     *
     * @return void
     */
    protected function _prepare_page()
    {
        $this->get_collection()->set_page_size((int) $this->get_param($this->get_var_name_limit(), $this->_default_limit));
        $this->get_collection()->set_cur_page((int) $this->get_param($this->get_var_name_page(), $this->_default_page));
    }
    /**
     * Initialize grid
     *
     * @return void
     */
    protected function _prepare_grid()
    {
        $this->_event_manager->dispatch('backend_block_widget_grid_prepare_grid_before', ['grid' => $this, 'collection' => $this->get_collection()]);
        if ($this->get_child_block('grid.massaction') && $this->get_child_block('grid.massaction')->is_available()) {
            $this->get_child_block('grid.massaction')->prepare_massaction_column();
        }
        $this->_prepare_collection();
        if ($this->has_column_renderers()) {
            foreach ($this->get_column_renderers() as $renderer => $renderer_class) {
                $this->get_column_set()->set_renderer_type($renderer, $renderer_class);
            }
        }
        if ($this->has_column_filters()) {
            foreach ($this->get_column_filters() as $filter => $filter_class) {
                $this->get_column_set()->set_filter_type($filter, $filter_class);
            }
        }
        $this->get_column_set()->set_sortable($this->get_sortable());
        $this->_prepare_filter_buttons();
    }
    /**
     * Get massaction block
     *
     * @return bool|\Magento\Framework\View\Element\AbstractBlock
     */
    public function get_massaction_block()
    {
        return $this->get_child_block('grid.massaction');
    }
    /**
     * Prepare grid filter buttons
     *
     * @return void
     */
    protected function _prepare_filter_buttons()
    {
        $this->set_child('reset_filter_button', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => __('Reset Filter'), 'onclick' => $this->get_js_object_name() . '.resetFilter()', 'class' => 'action-reset action-tertiary'])->set_data_attribute(['action' => 'grid-filter-reset']));
        $this->set_child('search_button', $this->get_layout()->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => __('Search'), 'onclick' => $this->get_js_object_name() . '.doFilter()', 'class' => 'action-secondary'])->set_data_attribute(['action' => 'grid-filter-apply']));
    }
    /**
     * Initialize grid before rendering
     *
     * @return $this
     */
    protected function _before_to_html()
    {
        $this->_prepare_grid();
        return parent::_before_to_html();
    }
    /**
     * Retrieve limit request key
     *
     * @return string
     */
    public function get_var_name_limit()
    {
        return $this->_var_name_limit;
    }
    /**
     * Retrieve page request key
     *
     * @return string
     */
    public function get_var_name_page()
    {
        return $this->_var_name_page;
    }
    /**
     * Retrieve sort request key
     *
     * @return string
     */
    public function get_var_name_sort()
    {
        return $this->_var_name_sort;
    }
    /**
     * Retrieve sort direction request key
     *
     * @return string
     */
    public function get_var_name_dir()
    {
        return $this->_var_name_dir;
    }
    /**
     * Retrieve filter request key
     *
     * @return string
     */
    public function get_var_name_filter()
    {
        return $this->_var_name_filter;
    }
    /**
     * Set Limit request key
     *
     * @param string $name
     * @return $this
     */
    public function set_var_name_limit($name)
    {
        $this->_var_name_limit = $name;
        return $this;
    }
    /**
     * Set Page request key
     *
     * @param string $name
     * @return $this
     */
    public function set_var_name_page($name)
    {
        $this->_var_name_page = $name;
        return $this;
    }
    /**
     * Set Sort request key
     *
     * @param string $name
     * @return $this
     */
    public function set_var_name_sort($name)
    {
        $this->_var_name_sort = $name;
        return $this;
    }
    /**
     * Set Sort Direction request key
     *
     * @param string $name
     * @return $this
     */
    public function set_var_name_dir($name)
    {
        $this->_var_name_dir = $name;
        return $this;
    }
    /**
     * Set Filter request key
     *
     * @param string $name
     * @return $this
     */
    public function set_var_name_filter($name)
    {
        $this->_var_name_filter = $name;
        return $this;
    }
    /**
     * Set visibility of pager
     *
     * @param bool $visible
     * @return $this
     */
    public function set_pager_visibility($visible = true)
    {
        $this->_pager_visibility = $visible;
        return $this;
    }
    /**
     * Return visibility of pager
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_pager_visibility()
    {
        return $this->_pager_visibility;
    }
    /**
     * Set visibility of message blocks
     *
     * @param bool $visible
     * @return void
     */
    public function set_message_block_visibility($visible = true)
    {
        $this->_message_block_visibility = $visible;
    }
    /**
     * Return visibility of message blocks
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_message_block_visibility()
    {
        return $this->_message_block_visibility;
    }
    /**
     * Set default limit
     *
     * @param int $limit
     * @return $this
     */
    public function set_default_limit($limit)
    {
        $this->_default_limit = $limit;
        return $this;
    }
    /**
     * Set default page
     *
     * @param int $page
     * @return $this
     */
    public function set_default_page($page)
    {
        $this->_default_page = $page;
        return $this;
    }
    /**
     * Set default sort
     *
     * @param string $sort
     * @return $this
     */
    public function set_default_sort($sort)
    {
        $this->_default_sort = $sort;
        return $this;
    }
    /**
     * Set default direction
     *
     * @param string $dir
     * @return $this
     */
    public function set_default_dir($dir)
    {
        $this->_default_dir = $dir;
        return $this;
    }
    /**
     * Set default filter
     *
     * @param string $filter
     * @return $this
     */
    public function set_default_filter($filter)
    {
        $this->_default_filter = $filter;
        return $this;
    }
    /**
     * Check whether grid container should be displayed
     *
     * @return bool
     */
    public function can_display_container()
    {
        if ($this->get_request()->get_query('ajax')) {
            return false;
        }
        return true;
    }
    /**
     * Retrieve grid reload url
     *
     * @return string;
     */
    public function get_grid_url()
    {
        return $this->has_data('grid_url') ? $this->get_data('grid_url') : $this->get_absolute_grid_url();
    }
    /**
     * Grid url getter
     *
     * Version of getGridUrl() but with parameters
     *
     * @param array $params url parameters
     * @return string current grid url
     */
    public function get_absolute_grid_url($params = [])
    {
        return $this->get_current_url($params);
    }
    /**
     * Retrieve grid
     *
     * @param string $paramName
     * @param mixed $default
     * @return mixed
     */
    public function get_param($param_name, $default = null)
    {
        $session_param_name = $this->get_id() . $param_name;
        if ($this->get_request()->has($param_name)) {
            $param = $this->get_request()->get_param($param_name);
            if ($this->_save_parameters_in_session) {
                $this->_backend_session->set_data($session_param_name, $param);
            }
            return $param;
        } elseif ($this->_save_parameters_in_session && $param = $this->_backend_session->get_data($session_param_name)) {
            return $param;
        }
        return $default;
    }
    /**
     * Set whether grid parameters should be saved in session
     *
     * @param bool $flag
     * @return $this
     */
    public function set_save_parameters_in_session($flag)
    {
        $this->_save_parameters_in_session = $flag;
        return $this;
    }
    /**
     * Retrieve grid javascript object name
     *
     * @return string
     */
    public function get_js_object_name()
    {
        return preg_replace('~[^a-z0-9_]*~i', '', $this->get_id()) . 'JsObject';
    }
    /**
     * Set count totals
     *
     * @param bool $count
     * @return $this
     */
    public function set_count_totals($count = true)
    {
        $this->_count_totals = $count;
        return $this;
    }
    /**
     * Return count totals
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_count_totals()
    {
        return $this->_count_totals;
    }
    /**
     * Set totals
     *
     * @param \Magento\Framework\DataObject $totals
     * @return void
     */
    public function set_totals(\Magento\Framework\Data_Object $totals)
    {
        $this->_var_totals = $totals;
    }
    /**
     * Retrieve totals
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_totals()
    {
        return $this->_var_totals;
    }
    /**
     * Generate list of grid buttons
     *
     * @return string
     */
    public function get_main_buttons_html()
    {
        $html = '';
        if ($this->get_column_set()->is_filter_visible()) {
            $html .= $this->get_search_button_html();
            $html .= $this->get_reset_filter_button_html();
        }
        return $html;
    }
    /**
     * Generate reset button
     *
     * @return string
     */
    public function get_reset_filter_button_html()
    {
        return $this->get_child_html('reset_filter_button');
    }
    /**
     * Generate search button
     *
     * @return string
     */
    public function get_search_button_html()
    {
        return $this->get_child_html('search_button');
    }
}