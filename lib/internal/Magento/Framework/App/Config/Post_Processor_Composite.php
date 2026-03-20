<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Spi\Post_Processor_Interface;
/**
 * @inheritdoc
 * @package Magento\Framework\App\Config
 */
class Post_Processor_Composite implements Post_Processor_Interface
{
    /**
     * @var \Magento\Framework\App\Config\Spi\PostProcessorInterface[]
     */
    private $processors;
    /**
     * @param array $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }
    /**
     * @param array $config
     * @return array
     */
    public function process(array $config)
    {
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }
        return $config;
    }
}