<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Api\Data;

/**
 * Represents Data Object for a Product Frontend Action like Product View or Comparison
 *
 * @api
 * @since 102.0.0
 */
interface Product_Frontend_Action_Interface
{
    /**
     * Gets Identifier of a Product Frontend Action
     *
     * @return int
     * @since 102.0.0
     */
    public function get_action_id();
    /**
     * Sets Identifier of a Product Frontend Action
     *
     * @param int $actionId
     * @return void
     * @since 102.0.0
     */
    public function set_action_id($action_id);
    /**
     * Gets Identifier of Visitor who performs a Product Frontend Action
     *
     * @return int
     * @since 102.0.0
     */
    public function get_visitor_id();
    /**
     * Sets Identifier of Visitor who performs a Product Frontend Action
     *
     * @param int $visitorId
     * @return void
     * @since 102.0.0
     */
    public function set_visitor_id($visitor_id);
    /**
     * Gets Identifier of Customer who performs a Product Frontend Action
     *
     * @return int
     * @since 102.0.0
     */
    public function get_customer_id();
    /**
     * Sets Identifier of Customer who performs Product Frontend Action
     *
     * @param int $customerId
     * @return void
     * @since 102.0.0
     */
    public function set_customer_id($customer_id);
    /**
     * Gets Identifier of Product a Product Frontend Action is performed on
     *
     * @return int
     * @since 102.0.0
     */
    public function get_product_id();
    /**
     * Sets Identifier of Product a Product Frontend Action is performed on
     *
     * @param int $productId
     * @return void
     * @since 102.0.0
     */
    public function set_product_id($product_id);
    /**
     * Gets Identifier of Type of a Product Frontend Action
     *
     * @return string
     * @since 102.0.0
     */
    public function get_type_id();
    /**
     * Sets Identifier of Type of a Product Frontend Action
     *
     * @param string $typeId
     * @return void
     * @since 102.0.0
     */
    public function set_type_id($type_id);
    /**
     * Gets JS timestamp of a Product Frontend Action (in microseconds)
     *
     * @return int
     * @since 102.0.0
     */
    public function get_added_at();
    /**
     * Sets JS timestamp of a Product Frontend Action (in microseconds)
     *
     * @param int $addedAt
     * @return void
     * @since 102.0.0
     */
    public function set_added_at($added_at);
}