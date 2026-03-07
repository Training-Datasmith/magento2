<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\Store;

/**
 * Command notifies MBI about that data collection was finished.
 */
class NotifyDataChangedCommand implements CommandInterface
{
    private string $notifyDataChangedUrlPath = 'analytics/url/notify_data_changed';

    /**
     * NotifyDataChangedCommand constructor.
     */
    public function __construct(private readonly AnalyticsToken $analyticsToken, private readonly Http\ClientInterface $httpClient, private readonly ScopeConfigInterface $config, private readonly ResponseResolver $responseResolver)
    {
    }

    /**
     * Notify MBI about that data collection was finished
     */
    public function execute(): bool
    {
        $result = false;
        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                Request::METHOD_POST,
                $this->config->getValue($this->notifyDataChangedUrlPath),
                [
                    'access-token' => $this->analyticsToken->getToken(),
                    'url' => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                ]
            );
            $result = $this->responseResolver->getResult($response);
        }
        return (bool)$result;
    }
}
