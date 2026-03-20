<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response;

use Magento\Framework\App\Response\Header_Provider\Header_Provider_Interface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
class Header_Manager
{
    /**
     * @var HeaderProviderInterface[]
     */
    private $header_providers;
    /**
     * @param HeaderProviderInterface[] $headerProviderList
     * @throws LocalizedException In case one of the header providers is invalid
     */
    public function __construct($header_provider_list)
    {
        foreach ($header_provider_list as $header) {
            if (!$header instanceof Header_Provider_Interface) {
                throw new Localized_Exception(new Phrase('The header provider is invalid. Verify and try again.'));
            }
        }
        $this->header_providers = $header_provider_list;
    }
    /**
     * @param \Magento\Framework\App\Response\Http $subject
     * @return void
     * @codeCoverageIgnore
     */
    public function before_send_response(\Magento\Framework\App\Response\Http $subject)
    {
        foreach ($this->header_providers as $provider) {
            if ($provider->can_apply()) {
                $subject->set_header($provider->get_name(), $provider->get_value());
            }
        }
    }
}