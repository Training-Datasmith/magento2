<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Bundle\Model\Product;

use Magento\Bundle\Api\Data\Option_Interface;
use Magento\Bundle\Api\Product_Option_Repository_Interface as OptionRepository;
use Magento\Bundle\Model\Link;
use Magento\Framework\Exception\Input_Exception;
use Magento\Framework\Exception\No_Such_Entity_Exception;
/**
 * Check bundle product option link if exist
 */
class Check_Option_Link_If_Exist
{
    /**
     * @var OptionRepository
     */
    private $option_repository;
    /**
     * @param OptionRepository $optionRepository
     */
    public function __construct(Option_Repository $option_repository)
    {
        $this->option_repository = $option_repository;
    }
    /**
     * Check if link is already exist in bundle product option
     *
     * @param string $sku
     * @param OptionInterface $optionToDelete
     * @param Link $link
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(string $sku, Option_Interface $option_to_delete, Link $link): bool
    {
        $is_link_exist = true;
        $available_options = $this->get_available_options_after_delete($sku, $option_to_delete);
        $option_link_ids = $this->get_link_ids($available_options);
        if (in_array($link->get_entity_id(), $option_link_ids)) {
            $is_link_exist = false;
        }
        return $is_link_exist;
    }
    /**
     * Retrieve bundle product options after delete option
     *
     * @param string $sku
     * @param OptionInterface $optionToDelete
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function get_available_options_after_delete(string $sku, Option_Interface $option_to_delete): array
    {
        $bundle_product_options = $this->option_repository->get_list($sku);
        $options = [];
        foreach ($bundle_product_options as $bundle_option) {
            if ($bundle_option->get_option_id() == $option_to_delete->get_option_id()) {
                continue;
            }
            $options[] = $bundle_option;
        }
        return $options;
    }
    /**
     * Retrieve bundle product link options
     *
     * @param array $options
     * @return array
     */
    private function get_link_ids(array $options): array
    {
        $ids = [];
        foreach ($options as $option) {
            $links = $option->get_product_links();
            if (!empty($links)) {
                foreach ($links as $link) {
                    $ids[] = $link->get_entity_id();
                }
            }
        }
        return $ids;
    }
}