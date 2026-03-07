<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\GoogleOptimizer\Model\Plugin\Catalog\Category;

class DataProvider
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\GoogleOptimizer\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * Updates metadata.
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $result['category_view_optimization']['arguments']['data']['disabled'] =
            !$this->_helper->isGoogleExperimentActive();
        $result['category_view_optimization']['arguments']['data']['config']['componentType'] =
            \Magento\Ui\Component\Form\Fieldset::NAME;
        $result['category_view_optimization']['arguments']['data']['config']['label'] = '';

        return $result;
    }
}
