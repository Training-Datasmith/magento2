<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Search;

/**
 * Search Customer Model
 *
 * @method Customer setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Customer setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method Customer setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method Customer setResults(array $results)
 * @method array getResults()
 * @api
 * @since 100.0.2
 */
class Customer extends \Magento\Framework\Data_Object
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtml_data = null;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customer_repository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $search_criteria_builder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filter_builder;
    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customer_view_helper;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Helper\View $customerViewHelper
     */
    public function __construct(\Magento\Backend\Helper\Data $adminhtml_data, \Magento\Customer\Api\Customer_Repository_Interface $customer_repository, \Magento\Framework\Api\Search_Criteria_Builder $search_criteria_builder, \Magento\Framework\Api\Filter_Builder $filter_builder, \Magento\Customer\Helper\View $customer_view_helper)
    {
        $this->_adminhtml_data = $adminhtml_data;
        $this->customer_repository = $customer_repository;
        $this->search_criteria_builder = $search_criteria_builder;
        $this->filter_builder = $filter_builder;
        $this->_customer_view_helper = $customer_view_helper;
    }
    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->has_start() || !$this->has_limit() || !$this->has_query()) {
            $this->set_results($result);
            return $this;
        }
        $this->search_criteria_builder->set_current_page($this->get_start());
        $this->search_criteria_builder->set_page_size($this->get_limit());
        $search_fields = ['firstname', 'lastname', 'billing_company'];
        $filters = [];
        foreach ($search_fields as $field) {
            $filters[] = $this->filter_builder->set_field($field)->set_condition_type('like')->set_value($this->get_query() . '%')->create();
        }
        $this->search_criteria_builder->add_filters($filters);
        $search_criteria = $this->search_criteria_builder->create();
        $search_results = $this->customer_repository->get_list($search_criteria);
        foreach ($search_results->get_items() as $customer) {
            $customer_addresses = $customer->get_addresses();
            /** Look for a company name defined in default billing address */
            $company = null;
            foreach ($customer_addresses as $customer_address) {
                if ($customer_address->get_id() == $customer->get_default_billing()) {
                    $company = $customer_address->get_company();
                    break;
                }
            }
            $result[] = ['id' => 'customer/1/' . $customer->get_id(), 'type' => __('Customer'), 'name' => $this->_customer_view_helper->get_customer_name($customer), 'description' => $company, 'url' => $this->_adminhtml_data->get_url('customer/index/edit', ['id' => $customer->get_id()])];
        }
        $this->set_results($result);
        return $this;
    }
}