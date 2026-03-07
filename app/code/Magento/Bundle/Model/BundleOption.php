<?php

declare(strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class BundleOption extends AbstractExtensibleModel implements BundleOptionInterface
{
    /**#@+
     * Constants
     */
    public const OPTION_ID = 'option_id';
    public const OPTION_QTY = 'option_qty';
    public const OPTION_SELECTIONS = 'option_selections';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getOptionId()
    {
        return $this->getData(self::OPTION_ID);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getOptionQty()
    {
        return $this->getData(self::OPTION_QTY);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getOptionSelections()
    {
        return $this->getData(self::OPTION_SELECTIONS);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setOptionId($optionId)
    {
        return $this->setData(self::OPTION_ID, $optionId);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setOptionQty($optionQty)
    {
        return $this->setData(self::OPTION_QTY, $optionQty);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setOptionSelections(array $optionSelections)
    {
        return $this->setData(self::OPTION_SELECTIONS, $optionSelections);
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\BundleOptionExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
