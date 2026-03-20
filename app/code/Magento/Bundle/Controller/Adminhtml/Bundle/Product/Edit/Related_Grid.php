<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\Related_Grid as CatalogRelatedGrid;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
/**
 * Class RelatedGrid
 *
 * @package Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit
 * @deprecated Not used since related products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/related_product_listing.xml
 */
class Related_Grid extends Catalog_Related_Grid implements Http_Post_Action_Interface
{
}