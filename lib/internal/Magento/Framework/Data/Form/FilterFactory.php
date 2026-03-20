<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Data\Form;

use Magento\Framework\Data\Form\Filter\Filter_Interface;
use Magento\Framework\Object_Manager_Interface;
class Filter_Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $object_manager;
    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(Object_Manager_Interface $object_manager)
    {
        $this->object_manager = $object_manager;
    }
    /**
     * Create filter instance
     *
     * @param string $filterCode
     * @param array $data
     * @return FilterInterface
     */
    public function create($filter_code, array $data = [])
    {
        $filter_class = 'Magento\Framework\Data\Form\Filter\\' . ucfirst($filter_code);
        $filter = $this->object_manager->create($filter_class, $data);
        if (!$filter instanceof Filter_Interface) {
            throw new \InvalidArgumentException(sprintf('%s class must implement %s', $filter_class, \Magento\Framework\Data\Form\Filter\Filter_Interface::class));
        }
        return $filter;
    }
}