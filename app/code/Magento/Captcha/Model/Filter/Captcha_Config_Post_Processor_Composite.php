<?php

/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Captcha\Model\Filter;

use Magento\Captcha\Api\Captcha_Config_Post_Processor_Interface;
/**
 * Composite class for post processing captcha configuration
 */
class Captcha_Config_Post_Processor_Composite implements Captcha_Config_Post_Processor_Interface
{
    /**
     * @var CaptchaConfigPostProcessorInterface[] $processors
     */
    private $processors = [];
    /**
     * @param CaptchaConfigPostProcessorInterface[] $processors
     */
    public function __construct($processors = [])
    {
        $this->processors = $processors;
    }
    /**
     * Loops through all leafs of the composite and calls process method
     *
     * @param array $config
     * @return array
     */
    public function process(array $config): array
    {
        $result = [];
        foreach ($this->processors as $processor) {
            $result = array_merge_recursive($result, $processor->process($config));
        }
        return $result;
    }
}