<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdvancedSearch\Model\Client;

class ClientFactory implements ClientFactoryInterface
{
    /**
     * @param string $clientClass
     * @param string|null $openSearch
     */
    public function __construct(
        /**
         * Object var
         */
        protected \Magento\Framework\ObjectManagerInterface $objectManager,
        private $clientClass,
        protected \Magento\AdvancedSearch\Helper\Data $helper,
        private $openSearch = null
    ) {
    }

    /**
     * Return search client
     *
     * @return ClientInterface
     */
    public function create(array $options = [])
    {
        $class = $this->clientClass;
        if ($this->helper->isClientOpenSearchV2()) {
            $class = $this->openSearch;
        }

        return $this->objectManager->create(
            $class,
            ['options' => $options]
        );
    }
}
