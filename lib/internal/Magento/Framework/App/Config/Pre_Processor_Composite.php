<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Config;

use Magento\Framework\App\Config\Spi\Pre_Processor_Interface;
/**
 * Class PreProcessorComposite
 */
class Pre_Processor_Composite implements Pre_Processor_Interface
{
    /**
     * @var PreProcessorInterface[]
     */
    private $processors = [];
    /**
     * @param PreProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
    }
    /**
     * @inheritdoc
     */
    public function process(array $config)
    {
        /** @var PreProcessorInterface $processor */
        foreach ($this->processors as $processor) {
            $config = $processor->process($config);
        }
        return $config;
    }
}