<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\OfflinePayments\Block\Form;

class Purchaseorder extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Magento_OfflinePayments::form/purchaseorder.phtml';
}
