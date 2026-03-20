<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Acl\Acl_Resource\Config;

/**
 * ACL resources configuration schema locator
 */
class Schema_Locator implements \Magento\Framework\Config\Schema_Locator_Interface
{
    /**
     * @var \Magento\Framework\Config\Dom\UrnResolver
     */
    protected $urn_resolver;
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     */
    public function __construct(\Magento\Framework\Config\Dom\Urn_Resolver $urn_resolver)
    {
        $this->urn_resolver = $urn_resolver;
    }
    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get_schema()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:Acl/etc/acl_merged.xsd');
    }
    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get_per_file_schema()
    {
        return $this->urn_resolver->get_real_path('urn:magento:framework:Acl/etc/acl.xsd');
    }
}