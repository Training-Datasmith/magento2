<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\Crosssell_Grid as CatalogCrosssellGrid;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
/**
 * Class CrosssellGrid
 *
 * @package Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit
 * @deprecated Not used since cross-sell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/crosssell_product_listing.xml
 */
class Crosssell_Grid extends Catalog_Crosssell_Grid implements Http_Post_Action_Interface
{
}