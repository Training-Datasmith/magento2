<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Config\Source;

/**
 * A source model for verticals configuration.
 *
 * Prepares and provides options for a selector of verticals which is located
 * in the corresponding configuration menu of the Admin area.
 */
class Vertical implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
        /**
         * The list of possible verticals.
         *
         * This list is configured via di.xml and may be extended or changed
         * in any module if it is needed.
         *
         * It is supposed that the list may be changed in each Magento release.
         */
        private readonly array $verticals
    ) {
    }

    /**
     * @inheritdoc
     * @return mixed[]
     */
    public function toOptionArray(): array
    {
        $result = [
            ['value' => '', 'label' => __('--Please Select--')],
        ];

        foreach ($this->verticals as $vertical) {
            $result[] = ['value' => $vertical, 'label' => __($vertical)];
        }

        return $result;
    }
}
