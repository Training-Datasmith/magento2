<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Abstract_Filter;
/**
 * Grid column block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 */
class Column extends Widget
{
    /**
     * Parent grid
     *
     * @var \Magento\Backend\Block\Widget\Grid
     */
    protected $_grid;
    /**
     * Column renderer
     *
     * @var \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
     */
    protected $_renderer;
    /**
     * Column filter
     *
     * @var AbstractFilter
     */
    protected $_filter;
    /**
     * Column css classes
     *
     * @var string|null
     */
    protected $_css_class = null;
    /**
     * The renderer types
     *
     * @var array
     */
    protected $_renderer_types = ['action' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action::class, 'button' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Button::class, 'checkbox' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Checkbox::class, 'concat' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Concat::class, 'country' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Country::class, 'currency' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Currency::class, 'date' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Date::class, 'datetime' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Datetime::class, 'default' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text::class, 'draggable-handle' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Draggable_Handle::class, 'input' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input::class, 'massaction' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Massaction::class, 'number' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Number::class, 'options' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options::class, 'price' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Price::class, 'radio' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Radio::class, 'select' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Select::class, 'store' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Store::class, 'text' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Longtext::class, 'wrapline' => \Magento\Backend\Block\Widget\Grid\Column\Renderer\Wrapline::class];
    /**
     * The filter types
     *
     * @var array
     */
    protected $_filter_types = ['datetime' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Datetime::class, 'date' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Date::class, 'range' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Range::class, 'number' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Range::class, 'currency' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Range::class, 'price' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Price::class, 'country' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Country::class, 'options' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Select::class, 'massaction' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Massaction::class, 'checkbox' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Checkbox::class, 'radio' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Radio::class, 'skip-list' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Skip_List::class, 'store' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Store::class, 'theme' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Theme::class, 'default' => \Magento\Backend\Block\Widget\Grid\Column\Filter\Text::class];
    /**
     * Column is grouped
     * @var bool
     */
    protected $_is_grouped = false;
    /**
     * Set property is grouped.
     *
     * @return void
     */
    public function _construct()
    {
        if ($this->has_data('grouped')) {
            $this->_is_grouped = (bool) $this->get_data('grouped');
        }
        parent::_construct();
    }
    /**
     * Should column be displayed in grid
     *
     * @return bool
     */
    public function is_displayed()
    {
        return true;
    }
    /**
     * Set grid block to column
     *
     * @param \Magento\Backend\Block\Widget\Grid $grid
     * @return $this
     */
    public function set_grid($grid)
    {
        $this->_grid = $grid;
        // Init filter object
        $this->get_filter();
        return $this;
    }
    /**
     * Get grid block
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    public function get_grid()
    {
        return $this->_grid;
    }
    /**
     * Retrieve html id of filter
     *
     * @return string
     */
    public function get_html_id()
    {
        return $this->get_grid()->get_id() . '_' . $this->get_grid()->get_var_name_filter() . '_' . $this->get_id();
    }
    /**
     * Get html code for column properties
     *
     * @return string
     */
    public function get_html_property()
    {
        return $this->get_renderer()->render_property();
    }
    /**
     * This method get Header html.
     *
     * @return string
     */
    public function get_header_html()
    {
        return $this->get_renderer()->render_header();
    }
    /**
     * Get column css classes
     *
     * @return string
     */
    public function get_css_class()
    {
        if ($this->_css_class === null) {
            if ($this->get_align()) {
                $this->_css_class .= 'a-' . $this->get_align();
            }
            // Add a custom css class for column
            if ($this->has_data('column_css_class')) {
                $this->_css_class .= ' ' . $this->get_data('column_css_class');
            }
            if ($this->get_editable()) {
                $this->_css_class .= ' editable';
            }
            $this->_css_class .= ' col-' . $this->get_id();
        }
        return $this->_css_class;
    }
    /**
     * Get column css property
     *
     * @return string
     */
    public function get_css_property()
    {
        return $this->get_renderer()->render_css();
    }
    /**
     * Set is column sortable
     *
     * @param bool $value
     * @return void
     */
    public function set_sortable($value)
    {
        $this->set_data('sortable', $value);
    }
    /**
     * Get header css class name.
     *
     * @return string
     */
    public function get_header_css_class()
    {
        $class = $this->get_data('header_css_class');
        $class .= false === $this->get_sortable() ? ' no-link' : '';
        $class .= ' col-' . $this->get_id();
        return $class;
    }
    /**
     * This method check if is sortable.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function get_sortable()
    {
        return $this->has_data('sortable') ? (bool) $this->get_data('sortable') : true;
    }
    /**
     * Add css class to column header
     *
     * @param string $className
     * @return void
     */
    public function add_header_css_class($class_name)
    {
        $classes = $this->get_data('header_css_class') ? $this->get_data('header_css_class') . ' ' : '';
        $this->set_data('header_css_class', $classes . $class_name);
    }
    /**
     * Get header class names
     *
     * @return string
     */
    public function get_header_html_property()
    {
        $str = '';
        if ($class = $this->get_header_css_class()) {
            $str .= ' class="' . $class . '"';
        }
        return $str;
    }
    /**
     * Retrieve row column field value for display
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function get_row_field(\Magento\Framework\Data_Object $row)
    {
        $rendered_value = $this->get_renderer()->render($row);
        if ($this->get_html_decorators()) {
            $rendered_value = $this->_apply_decorators($rendered_value, $this->get_html_decorators());
        }
        /*
         * if column has determined callback for framing call
         * it before give away rendered value
         *
         * callback_function($renderedValue, $row, $column, $isExport)
         * should return new version of rendered value
         */
        $frame_callback = $this->get_frame_callback();
        if (is_array($frame_callback)) {
            $this->validate_frame_callback($frame_callback);
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            $rendered_value = call_user_func($frame_callback, $rendered_value, $row, $this, false);
        }
        return $rendered_value;
    }
    /**
     * Validate frame callback
     *
     * @throws \InvalidArgumentException
     *
     * @param array $callback
     * @return void
     */
    private function validate_frame_callback(array $callback)
    {
        if (!is_object($callback[0]) || !$callback[0] instanceof Widget) {
            throw new \InvalidArgumentException('Frame callback host must be instance of Magento\Backend\Block\Widget');
        }
    }
    /**
     * Retrieve row column field value for export
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function get_row_field_export(\Magento\Framework\Data_Object $row)
    {
        $rendered_value = $this->get_renderer()->render_export($row);
        /*
         * if column has determined callback for framing call
         * it before give away rendered value
         *
         * callback_function($renderedValue, $row, $column, $isExport)
         * should return new version of rendered value
         */
        $frame_callback = $this->get_frame_callback();
        if (is_array($frame_callback)) {
            $this->validate_frame_callback($frame_callback);
            //phpcs:ignore Magento2.Functions.DiscouragedFunction
            $rendered_value = call_user_func($frame_callback, $rendered_value, $row, $this, true);
        }
        return $rendered_value;
    }
    /**
     * Retrieve Header Name for Export
     *
     * @return string
     */
    public function get_export_header()
    {
        if ($this->get_header_export()) {
            return $this->get_header_export();
        }
        return $this->get_header();
    }
    /**
     * Decorate rendered cell value
     *
     * @param string $value
     * @param array|string $decorators
     * @return string
     */
    protected function &_apply_decorators($value, $decorators)
    {
        if (!is_array($decorators)) {
            if (is_string($decorators)) {
                $decorators = explode(' ', $decorators);
            }
        }
        if (!is_array($decorators) || empty($decorators)) {
            return $value;
        }
        switch (array_shift($decorators)) {
            case 'nobr':
                $value = '<span class="nobr">' . $value . '</span>';
                break;
        }
        if (!empty($decorators)) {
            return $this->_apply_decorators($value, $decorators);
        }
        return $value;
    }
    /**
     * Set column renderer
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer $renderer
     * @return $this
     */
    public function set_renderer($renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }
    /**
     * Set renderer type class name
     *
     * @param string $type type of renderer
     * @param string $className renderer class name
     * @return void
     */
    public function set_renderer_type($type, $class_name)
    {
        $this->_renderer_types[$type] = $class_name;
    }
    /**
     * Get renderer class name by renderer type
     *
     * @return string
     */
    protected function _get_renderer_by_type()
    {
        $type = strtolower((string) $this->get_type());
        return $this->_renderer_types[$type] ?? $this->_renderer_types['default'];
    }
    /**
     * Retrieve column renderer
     *
     * @return \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
     */
    public function get_renderer()
    {
        if ($this->_renderer === null) {
            $renderer_class = $this->get_data('renderer');
            if (empty($renderer_class)) {
                $renderer_class = $this->_get_renderer_by_type();
            }
            $this->_renderer = $this->get_layout()->create_block($renderer_class)->set_column($this);
        }
        return $this->_renderer;
    }
    /**
     * Set column filter
     *
     * @param string $filterClass filter class name
     * @return void
     */
    public function set_filter($filter_class)
    {
        $filter_block = $this->get_layout()->create_block($filter_class);
        $filter_block->set_column($this);
        $this->_filter = $filter_block;
    }
    /**
     * Set filter type class name
     *
     * @param string $type type of filter
     * @param string $className filter class name
     * @return void
     */
    public function set_filter_type($type, $class_name)
    {
        $this->_filter_types[$type] = $class_name;
    }
    /**
     * Get column filter class name by filter type
     *
     * @return string
     */
    protected function _get_filter_by_type()
    {
        $type = $this->get_filter_type() ? strtolower($this->get_filter_type()) : strtolower((string) $this->get_type());
        return $this->_filter_types[$type] ?? $this->_filter_types['default'];
    }
    /**
     * Get filter block
     *
     * @return AbstractFilter|false
     */
    public function get_filter()
    {
        if ($this->_filter === null) {
            $filter_class = $this->get_data('filter');
            if (false === (bool) $filter_class && false === ($filter_class === null)) {
                return false;
            }
            if (!$filter_class) {
                $filter_class = $this->_get_filter_by_type();
                if ($filter_class === false) {
                    return false;
                }
            }
            $this->_filter = $this->get_layout()->create_block($filter_class)->set_column($this);
        }
        return $this->_filter;
    }
    /**
     * Get filter html code
     *
     * @return null|string
     */
    public function get_filter_html()
    {
        $filter = $this->get_filter();
        $output = $filter ? $filter->get_html() : '&nbsp;';
        return $output;
    }
    /**
     * Check if column is grouped
     *
     * @return bool
     */
    public function is_grouped()
    {
        return $this->_is_grouped;
    }
}