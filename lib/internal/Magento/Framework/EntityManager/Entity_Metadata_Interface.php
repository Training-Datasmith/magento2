<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Entity_Manager;

/**
 * Interface EntityMetadataInterface
 */
interface Entity_Metadata_Interface
{
    /**
     * @return string
     */
    public function get_identifier_field();
    /**
     * @return string
     */
    public function get_link_field();
    /**
     * @return string
     */
    public function get_entity_table();
    /**
     * @return string
     */
    public function get_entity_connection_name();
    /**
     * @return null|string
     */
    public function generate_identifier();
    /**
     * @return string[]
     */
    public function get_entity_context();
    /**
     * @return null|string
     */
    public function get_eav_entity_type();
    /**
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @deprecated 100.1.0
     */
    public function get_entity_connection();
}