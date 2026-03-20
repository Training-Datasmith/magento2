<?php

/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Data\Collection;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Data\Collection;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem\Directory\Write_Interface;
/**
 * Filesystem items collection
 *
 * Can scan a folder for files and/or folders recursively.
 * Creates \Magento\Framework\DataObject instance for each item, with its filename and base name
 *
 * Supports regexp masks that are applied to files and folders base names.
 * These masks apply before adding items to collection, during filesystem scanning
 *
 * Supports dirsFirst feature, that will make directories be before files, regardless of sorting column.
 *
 * Supports some fancy filters.
 *
 * At least one target directory must be set
 *
 * @api
 * @since 100.0.2
 */
class Filesystem extends \Magento\Framework\Data\Collection
{
    /**
     * Target directory.
     *
     * @var string
     */
    protected $_target_dirs = [];
    /**
     * Whether to collect files.
     *
     * @var bool
     */
    protected $_collect_files = true;
    /**
     * Whether to collect directories before files.
     *
     * @var bool
     */
    protected $_dirs_first = true;
    /**
     * Whether to collect recursively.
     *
     * @var bool
     */
    protected $_collect_recursively = true;
    /**
     * Whether to collect dirs.
     *
     * @var bool
     */
    protected $_collect_dirs = false;
    /**
     * \Directory names regex pre-filter.
     *
     * @var string
     */
    protected $_allowed_dirs_mask = '/^[a-z0-9\.\-\_]+$/i';
    /**
     * Filenames regex pre-filter.
     *
     * @var string
     */
    protected $_allowed_files_mask = '/^[a-z0-9\.\-\_]+\.[a-z0-9]+$/i';
    /**
     * Disallowed filenames regex pre-filter match for better versatility.
     *
     * @var string
     */
    protected $_disallowed_files_mask = '';
    /**
     * Filter rendering helper variable.
     *
     * @var int
     * @see Collection::$_filter
     * @see Collection::$_isFiltersRendered
     */
    private $_filter_increment = 0;
    /**
     * Filter rendering helper variable.
     *
     * @var array
     * @see Collection::$_filter
     * @see Collection::$_isFiltersRendered
     */
    private $_filter_brackets = [];
    /**
     * Filter rendering helper variable.
     *
     * @var string
     * @see Collection::$_filter
     * @see Collection::$_isFiltersRendered
     */
    private $_filter_eval_rendered = '';
    /**
     * Collecting items helper variable.
     *
     * @var array
     */
    protected $_collected_dirs = [];
    /**
     * Collecting items helper variable.
     *
     * @var array
     */
    protected $_collected_files = [];
    /**
     * @var WriteInterface
     */
    private $root_directory;
    /**
     * @param EntityFactoryInterface|null $_entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(?Entity_Factory_Interface $_entity_factory = null, ?\Magento\Framework\Filesystem $filesystem = null)
    {
        $this->_entity_factory = $_entity_factory ?? Object_Manager::get_instance()->get(Entity_Factory_Interface::class);
        $filesystem = $filesystem ?? Object_Manager::get_instance()->get(\Magento\Framework\Filesystem::class);
        $this->root_directory = $filesystem->get_directory_write(Directory_List::ROOT);
        parent::__construct($this->_entity_factory);
    }
    /**
     * Allowed dirs mask setter. Set empty to not filter.
     *
     * @param string $regex
     * @return $this
     */
    public function set_dirs_filter($regex)
    {
        $this->_allowed_dirs_mask = (string) $regex;
        return $this;
    }
    /**
     * Allowed files mask setter. Set empty to not filter.
     *
     * @param string $regex
     * @return $this
     */
    public function set_files_filter($regex)
    {
        $this->_allowed_files_mask = (string) $regex;
        return $this;
    }
    /**
     * Disallowed files mask setter. Set empty value to not use this filter.
     *
     * @param string $regex
     * @return $this
     */
    public function set_disallowed_files_filter($regex)
    {
        $this->_disallowed_files_mask = (string) $regex;
        return $this;
    }
    /**
     * Set whether to collect dirs.
     *
     * @param bool $value
     * @return $this
     */
    public function set_collect_dirs($value)
    {
        $this->_collect_dirs = (bool) $value;
        return $this;
    }
    /**
     * Set whether to collect files.
     *
     * @param bool $value
     * @return $this
     */
    public function set_collect_files($value)
    {
        $this->_collect_files = (bool) $value;
        return $this;
    }
    /**
     * Set whether to collect recursively.
     *
     * @param bool $value
     * @return $this
     */
    public function set_collect_recursively($value)
    {
        $this->_collect_recursively = (bool) $value;
        return $this;
    }
    /**
     * Target directory setter. Adds directory to be scanned.
     *
     * @param string $value
     * @return $this
     * @throws \Exception
     */
    public function add_target_dir($value)
    {
        $value = (string) $value;
        if (!$this->root_directory->is_directory($value)) {
            throw new File_System_Exception(__('Unable to set target directory.'));
        }
        $this->_target_dirs[$value] = $value;
        return $this;
    }
    /**
     * Set whether to collect directories before files. Works *before* sorting.
     *
     * @param bool $value
     * @return $this
     */
    public function set_dirs_first($value)
    {
        $this->_dirs_first = (bool) $value;
        return $this;
    }
    /**
     * Get files from specified directory recursively (if needed).
     *
     * @param string|array $dir
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws FileSystemException
     */
    protected function _collect_recursive($dir)
    {
        $collected_result = [];
        if (!is_array($dir)) {
            $dir = [$dir];
        }
        foreach ($dir as $folder) {
            if ($nodes = $this->root_directory->search('/*', $folder)) {
                foreach ($nodes as $node) {
                    $collected_result[] = $this->root_directory->get_absolute_path($node);
                }
            }
        }
        if (empty($collected_result)) {
            return;
        }
        foreach ($collected_result as $item) {
            if ($this->root_directory->is_directory($item) && (!$this->_allowed_dirs_mask || preg_match($this->_allowed_dirs_mask, basename($item)))) {
                if ($this->_collect_dirs) {
                    if ($this->_dirs_first) {
                        $this->_collected_dirs[] = $item;
                    } else {
                        $this->_collected_files[] = $item;
                    }
                }
                if ($this->_collect_recursively) {
                    $this->_collect_recursive($item);
                }
            } elseif ($this->_collect_files && $this->root_directory->is_file($item) && (!$this->_allowed_files_mask || preg_match($this->_allowed_files_mask, basename($item))) && (!$this->_disallowed_files_mask || !preg_match($this->_disallowed_files_mask, basename($item)))) {
                $this->_collected_files[] = $item;
            }
        }
    }
    /**
     * Launch data collecting.
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function load_data($print_query = false, $log_query = false)
    {
        if ($this->is_loaded()) {
            return $this;
        }
        if (empty($this->_target_dirs)) {
            // phpcs:disable Magento2.Exceptions.DirectThrow
            throw new \Exception('Please specify at least one target directory.');
        }
        $this->_collected_files = [];
        $this->_collected_dirs = [];
        $this->_collect_recursive($this->_target_dirs);
        $this->_generate_and_filter_and_sort('_collectedFiles');
        if ($this->_dirs_first) {
            $this->_generate_and_filter_and_sort('_collectedDirs');
            $this->_collected_files = array_merge($this->_collected_dirs, $this->_collected_files);
        }
        // calculate totals
        $this->_total_records = count($this->_collected_files);
        $this->_set_is_loaded();
        // paginate and add items
        $from = ($this->get_cur_page() - 1) * $this->get_page_size();
        $to = $from + $this->get_page_size() - 1;
        $is_paginated = $this->get_page_size() > 0;
        $cnt = 0;
        foreach ($this->_collected_files as $row) {
            $cnt++;
            if ($is_paginated && ($cnt < $from || $cnt > $to)) {
                continue;
            }
            $item = new $this->_item_object_class();
            $this->add_item($item->add_data($row));
            if (!$item->has_id()) {
                $item->set_id($cnt);
            }
        }
        return $this;
    }
    /**
     * With specified collected items:
     *  - generate data
     *  - apply filters
     *  - sort
     *
     * @param string $attributeName '_collectedFiles' | '_collectedDirs'
     * @return void
     */
    private function _generate_and_filter_and_sort($attribute_name)
    {
        // generate custom data (as rows with columns) basing on the filenames
        foreach ($this->{$attribute_name} as $key => $filename) {
            $this->{$attribute_name}[$key] = $this->_generate_row($filename);
        }
        // apply filters on generated data
        if (!empty($this->_filters)) {
            foreach ($this->{$attribute_name} as $key => $row) {
                if (!$this->_filter_row($row)) {
                    unset($this->{$attribute_name}[$key]);
                }
            }
        }
        // sort (keys are lost!)
        if (!empty($this->_orders)) {
            usort($this->{$attribute_name}, [$this, '_usort']);
        }
    }
    /**
     * Callback for sorting items. Currently supports only sorting by one column.
     *
     * @param array $a
     * @param array $b
     * @return int|void
     */
    protected function _usort($a, $b)
    {
        foreach ($this->_orders as $key => $direction) {
            $result = $a[$key] > $b[$key] ? 1 : ($a[$key] < $b[$key] ? -1 : 0);
            return self::SORT_ORDER_ASC === strtoupper($direction) ? $result : -$result;
        }
    }
    /**
     * Set select order. Currently supports only sorting by one column.
     *
     * @param   string $field
     * @param   string $direction
     * @return  Collection
     */
    public function set_order($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orders = [$field => $direction];
        return $this;
    }
    /**
     * Generate item row basing on the filename.
     *
     * @param string $filename
     * @return array
     */
    protected function _generate_row($filename)
    {
        return ['filename' => $filename, 'basename' => basename($filename)];
    }
    /**
     * Set a custom filter with callback
     * The callback must take 3 params:
     *     string $field       - field key,
     *     mixed  $filterValue - value to filter by,
     *     array  $row         - a generated row (before generating varien objects)
     *
     * @param string $field
     * @param mixed $value
     * @param string $type 'and'|'or'
     * @param callable $callback
     * @param bool $isInverted
     * @return $this
     */
    public function add_callback_filter($field, $value, $type, $callback, $is_inverted = false)
    {
        $this->_filters[$this->_filter_increment] = ['field' => $field, 'value' => $value, 'is_and' => 'and' === $type, 'callback' => $callback, 'is_inverted' => $is_inverted];
        $this->_filter_increment++;
        return $this;
    }
    /**
     * The filters renderer and caller. Applies to each row, renders once.
     *
     * @param array $row
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filter_row($row)
    {
        // render filters once
        if (!$this->_is_filters_rendered) {
            $eval = '';
            for ($i = 0; $i < $this->_filter_increment; $i++) {
                if (isset($this->_filter_brackets[$i])) {
                    $eval .= $this->_render_condition_before_filter_element($i, $this->_filter_brackets[$i]['is_and']) . $this->_filter_brackets[$i]['value'];
                } else {
                    $f = '$this->_filters[' . $i . ']';
                    $eval .= $this->_render_condition_before_filter_element($i, $this->_filters[$i]['is_and']) . ($this->_filters[$i]['is_inverted'] ? '!' : '') . '$this->_invokeFilter(' . "{$f}['callback'], array({$f}['field'], {$f}['value'], " . '$row))';
                }
            }
            $this->_filter_eval_rendered = $eval;
            $this->_is_filters_rendered = true;
        }
        $result = false;
        if ($this->_filter_eval_rendered) {
            // phpcs:ignore Squiz.PHP.Eval
            eval('$result = ' . $this->_filter_eval_rendered . ';');
        }
        return $result;
    }
    /**
     * Invokes specified callback. Skips, if there is no filtered key in the row.
     *
     * @param callable $callback
     * @param array $callbackParams
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _invoke_filter($callback, $callback_params)
    {
        list($field, $value, $row) = $callback_params;
        if (!array_key_exists($field, $row)) {
            return false;
        }
        return call_user_func_array($callback, $callback_params);
    }
    /**
     * Fancy field filter.
     *
     * @param string $field
     * @param mixed $cond
     * @param string $type 'and' | 'or'
     * @see Db::addFieldToFilter()
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function add_field_to_filter($field, $cond, $type = 'and')
    {
        $inverted = true;
        // simply check whether equals
        if (!is_array($cond)) {
            return $this->add_callback_filter($field, $cond, $type, [$this, 'filterCallbackEq']);
        }
        // versatile filters
        if (isset($cond['from']) || isset($cond['to'])) {
            $this->_add_filter_bracket('(', 'and' === $type);
            if (isset($cond['from'])) {
                $this->add_callback_filter($field, $cond['from'], 'and', [$this, 'filterCallbackIsLessThan'], $inverted);
            }
            if (isset($cond['to'])) {
                $this->add_callback_filter($field, $cond['to'], 'and', [$this, 'filterCallbackIsMoreThan'], $inverted);
            }
            return $this->_add_filter_bracket(')');
        }
        if (isset($cond['eq'])) {
            return $this->add_callback_filter($field, $cond['eq'], $type, [$this, 'filterCallbackEq']);
        }
        if (isset($cond['neq'])) {
            return $this->add_callback_filter($field, $cond['neq'], $type, [$this, 'filterCallbackEq'], $inverted);
        }
        if (isset($cond['like'])) {
            return $this->add_callback_filter($field, $cond['like'], $type, [$this, 'filterCallbackLike']);
        }
        if (isset($cond['nlike'])) {
            return $this->add_callback_filter($field, $cond['nlike'], $type, [$this, 'filterCallbackLike'], $inverted);
        }
        if (isset($cond['in'])) {
            return $this->add_callback_filter($field, $cond['in'], $type, [$this, 'filterCallbackInArray']);
        }
        if (isset($cond['nin'])) {
            return $this->add_callback_filter($field, $cond['nin'], $type, [$this, 'filterCallbackInArray'], $inverted);
        }
        if (isset($cond['notnull'])) {
            return $this->add_callback_filter($field, $cond['notnull'], $type, [$this, 'filterCallbackIsNull'], $inverted);
        }
        if (isset($cond['null'])) {
            return $this->add_callback_filter($field, $cond['null'], $type, [$this, 'filterCallbackIsNull']);
        }
        if (isset($cond['moreq'])) {
            return $this->add_callback_filter($field, $cond['moreq'], $type, [$this, 'filterCallbackIsLessThan'], $inverted);
        }
        if (isset($cond['gt'])) {
            return $this->add_callback_filter($field, $cond['gt'], $type, [$this, 'filterCallbackIsMoreThan']);
        }
        if (isset($cond['lt'])) {
            return $this->add_callback_filter($field, $cond['lt'], $type, [$this, 'filterCallbackIsLessThan']);
        }
        if (isset($cond['gteq'])) {
            return $this->add_callback_filter($field, $cond['gteq'], $type, [$this, 'filterCallbackIsLessThan'], $inverted);
        }
        if (isset($cond['lteq'])) {
            return $this->add_callback_filter($field, $cond['lteq'], $type, [$this, 'filterCallbackIsMoreThan'], $inverted);
        }
        if (isset($cond['finset'])) {
            $filter_value = $cond['finset'] ? explode(',', $cond['finset']) : [];
            return $this->add_callback_filter($field, $filter_value, $type, [$this, 'filterCallbackInArray']);
        }
        // add OR recursively
        foreach ($cond as $or_cond) {
            $this->_add_filter_bracket('(', 'and' === $type);
            $this->add_field_to_filter($field, $or_cond, 'or');
            $this->_add_filter_bracket(')');
        }
        return $this;
    }
    /**
     * Prepare a bracket into filters.
     *
     * @param string $bracket
     * @param bool $isAnd
     * @return $this
     */
    protected function _add_filter_bracket($bracket = '(', $is_and = true)
    {
        $this->_filter_brackets[$this->_filter_increment] = ['value' => $bracket === ')' ? ')' : '(', 'is_and' => $is_and];
        $this->_filter_increment++;
        return $this;
    }
    /**
     * Render condition sign before element, if required.
     *
     * @param int $increment
     * @param bool $isAnd
     * @return string
     */
    protected function _render_condition_before_filter_element($increment, $is_and)
    {
        if (isset($this->_filter_brackets[$increment]) && ')' === $this->_filter_brackets[$increment]['value']) {
            return '';
        }
        $prev_increment = $increment - 1;
        $prev_bracket = false;
        if (isset($this->_filter_brackets[$prev_increment])) {
            $prev_bracket = $this->_filter_brackets[$prev_increment]['value'];
        }
        if ($prev_increment < 0 || $prev_bracket === '(') {
            return '';
        }
        return $is_and ? ' && ' : ' || ';
    }
    /**
     * Does nothing. Intentionally disabled parent method.
     *
     * @param string $field
     * @param string $value
     * @param string $type
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function add_filter($field, $value, $type = 'and')
    {
        return $this;
    }
    /**
     * Get all ids of collected items.
     *
     * @return array
     */
    public function get_all_ids()
    {
        return array_keys($this->_items);
    }
    /**
     * Callback method for 'like' fancy filter.
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filter_callback_like($field, $filter_value, $row)
    {
        // Forced to do this in order to keep backward compatibility for @api class.
        // Strict typing must be added to this method next major release.
        $filter_value = (string) $filter_value;
        $filter_value = trim(stripslashes($filter_value), '\'');
        $filter_value = trim($filter_value, '%');
        $filter_value_regex = '(.*?)' . preg_quote($filter_value, '/') . '(.*?)';
        return (bool) preg_match("/^{$filter_value_regex}\$/i", $row[$field]);
    }
    /**
     * Callback method for 'eq' fancy filter.
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filter_callback_eq($field, $filter_value, $row)
    {
        return $filter_value == $row[$field];
    }
    /**
     * Callback method for 'in' fancy filter.
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filter_callback_in_array($field, $filter_value, $row)
    {
        return in_array($row[$field], $filter_value);
    }
    /**
     * Callback method for 'isnull' fancy filter.
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filter_callback_is_null($field, $filter_value, $row)
    {
        return null === $row[$field];
    }
    /**
     * Callback method for 'moreq' fancy filter.
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filter_callback_is_more_than($field, $filter_value, $row)
    {
        return $row[$field] > $filter_value;
    }
    /**
     * Callback method for 'lteq' fancy filter.
     *
     * @param string $field
     * @param mixed $filterValue
     * @param array $row
     * @return bool
     * @see addFieldToFilter()
     * @see addCallbackFilter()
     */
    public function filter_callback_is_less_than($field, $filter_value, $row)
    {
        return $row[$field] < $filter_value;
    }
}