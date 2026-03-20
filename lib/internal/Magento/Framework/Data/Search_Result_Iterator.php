<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data;

use Magento\Framework\DB\Query_Interface;
class Search_Result_Iterator implements \Iterator
{
    /**
     * @var SearchResultInterface
     */
    protected $search_result;
    /**
     * @var QueryInterface
     */
    protected $query;
    /**
     * @var array
     */
    protected $current;
    /**
     * @var int
     */
    protected $key = 0;
    /**
     * @param AbstractSearchResult $searchResult
     * @param QueryInterface $query
     */
    public function __construct(Abstract_Search_Result $search_result, Query_Interface $query)
    {
        $this->search_result = $search_result;
        $this->query = $query;
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function current()
    {
        return $this->current;
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function next()
    {
        ++$this->key;
        $this->current = $this->search_result->create_data_object($this->query->fetch_item());
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function key()
    {
        return $this->key;
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function valid()
    {
        return !empty($this->current);
    }
    /**
     * @inheritdoc
     */
    #[\Return_Type_Will_Change]
    public function rewind()
    {
        $this->current = null;
        $this->key = 0;
        $this->query->reset();
        $this->next();
    }
}