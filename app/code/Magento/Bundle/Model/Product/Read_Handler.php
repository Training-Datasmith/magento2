<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Product_Option_Repository_Interface as OptionRepository;
use Magento\Framework\Entity_Manager\Operation\Extension_Interface;
/**
 * Class ReadHandler
 */
class Read_Handler implements Extension_Interface
{
    /**
     * @var OptionRepository
     */
    private $option_repository;
    /**
     * ReadHandler constructor.
     *
     * @param OptionRepository $optionRepository
     */
    public function __construct(Option_Repository $option_repository)
    {
        $this->option_repository = $option_repository;
    }
    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        if ($entity->get_type_id() != \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            return $entity;
        }
        $entity_extension = $entity->get_extension_attributes();
        $options = $this->option_repository->get_list_by_product($entity);
        if ($options) {
            $entity_extension->set_bundle_product_options($options);
        }
        $entity->set_extension_attributes($entity_extension);
        return $entity;
    }
}