<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Page;

/**
 * Adminhtml footer block
 *
 * @api
 * @since 100.0.2
 */
class Footer extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::page/footer.phtml';
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     * @since 100.1.0
     */
    protected $product_metadata;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\App\Product_Metadata_Interface $product_metadata, array $data = [])
    {
        $this->product_metadata = $product_metadata;
        parent::__construct($context, $data);
    }
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->set_show_profiler(true);
    }
    /**
     * Get product version
     *
     * @return string
     * @since 100.1.0
     */
    public function get_magento_version()
    {
        return $this->product_metadata->get_version();
    }
    /**
     * @inheritdoc
     * @since 101.0.0
     */
    protected function get_cache_lifetime()
    {
        return 3600 * 24 * 10;
    }
}