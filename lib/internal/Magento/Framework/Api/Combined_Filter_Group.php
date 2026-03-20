<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\Api;

use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Phrase;
/**
 * Groups two or more filters together using 'OR' or 'AND' strategy
 */
class Combined_Filter_Group extends Abstract_Simple_Object
{
    /**
     * Constants defined for keys of  data array
     */
    public const FILTERS = 'filters';
    public const COMBINATION_MODE = 'combination_mode';
    /**
     * Possible aggregation strategies for filters
     */
    public const COMBINED_WITH_AND = 'AND';
    public const COMBINED_WITH_OR = 'OR';
    /**
     * Returns a list of filters in this group
     *
     * @return \Magento\Framework\Api\Filter[]|null
     */
    public function get_filters()
    {
        $filters = $this->_get(self::FILTERS);
        return $filters === null ? [] : $filters;
    }
    /**
     * Set filters
     *
     * @param \Magento\Framework\Api\Filter[] $filters
     * @return $this
     * @codeCoverageIgnore
     */
    public function set_filters(?array $filters = null): self
    {
        return $this->set_data(self::FILTERS, $filters);
    }
    /**
     * @return mixed|null
     */
    public function get_combination_mode()
    {
        return $this->_get(self::COMBINATION_MODE);
    }
    /**
     * @param string $mode
     * @return $this
     * @throws InputException
     */
    public function set_combination_mode(string $mode): self
    {
        if ($mode !== self::COMBINED_WITH_AND && $mode !== self::COMBINED_WITH_OR) {
            throw new Input_Exception(new Phrase('Invalid combination mode: %1', [$mode]));
        }
        return $this->set_data(self::COMBINATION_MODE, $mode);
    }
}