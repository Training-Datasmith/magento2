<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Block;

use Magento\Advanced_Search\Model\Suggested_Queries_Interface;
use Magento\Framework\View\Element\Template;
use Magento\Search\Model\Query_Factory_Interface;
use Magento\Search\Model\Query_Interface;
abstract class Search_Data extends Template implements Search_Data_Interface
{
    /**
     * @var QueryInterface
     */
    private $query;
    /**
     * @var string
     */
    protected $_template = 'Magento_AdvancedSearch::search_data.phtml';
    /**
     * @param string $title
     */
    public function __construct(Template\Context $context, private readonly Suggested_Queries_Interface $search_data_provider, Query_Factory_Interface $query_factory, protected $title, array $data = [])
    {
        $this->query = $query_factory->get();
        parent::__construct($context, $data);
    }
    /**
     * @inheritdoc
     */
    public function get_items()
    {
        return $this->search_data_provider->get_items($this->query);
    }
    /**
     * @inheritdoc
     */
    public function is_show_results_count()
    {
        return $this->search_data_provider->is_results_count_enabled();
    }
    /**
     * @inheritdoc
     */
    public function get_link($query_text)
    {
        return $this->get_url('*/*/') . '?q=' . urlencode($query_text);
    }
    /**
     * @inheritdoc
     */
    public function get_title()
    {
        return __($this->title);
    }
}