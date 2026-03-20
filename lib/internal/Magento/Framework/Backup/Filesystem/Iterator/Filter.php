<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Filter \Iterator
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup\Filesystem\Iterator;

use Iterator;
class Filter extends \Filter_Iterator
{
    /**
     * Array that is used for filtering
     *
     * @var array
     */
    protected $_filters;
    /**
     * Constructor
     *
     * @param Iterator $iterator
     * @param array $filters list of files to skip
     */
    public function __construct(Iterator $iterator, array $filters)
    {
        parent::__construct($iterator);
        $this->_filters = $filters;
    }
    /**
     * Check whether the current element of the iterator is acceptable
     *
     * @return bool
     */
    #[\Return_Type_Will_Change]
    public function accept()
    {
        $current = str_replace('\\', '/', $this->current()->__toString() ?? '');
        $current_filename = str_replace('\\', '/', $this->current()->get_filename() ?? '');
        if ($current_filename == '.' || $current_filename == '..') {
            return false;
        }
        foreach ($this->_filters as $filter) {
            $filter = $filter !== null ? str_replace('\\', '/', $filter) : '';
            if (false !== strpos($current, $filter)) {
                return false;
            }
        }
        return true;
    }
}