<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Advanced_Search\Model\Client;

class Client_Factory implements Client_Factory_Interface
{
    /**
     * @param string $clientClass
     * @param string|null $openSearch
     */
    public function __construct(
        /**
         * Object var
         */
        protected \Magento\Framework\Object_Manager_Interface $object_manager,
        private $client_class,
        protected \Magento\Advanced_Search\Helper\Data $helper,
        private $open_search = null
    )
    {
    }
    /**
     * Return search client
     *
     * @return ClientInterface
     */
    public function create(array $options = [])
    {
        $class = $this->client_class;
        if ($this->helper->is_client_open_search_v2()) {
            $class = $this->open_search;
        }
        return $this->object_manager->create($class, ['options' => $options]);
    }
}