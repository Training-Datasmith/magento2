<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Command executes in case change store url
 */
class UpdateCommand implements CommandInterface
{
    private string $updateUrlPath = 'analytics/url/update';

    public function __construct(private readonly AnalyticsToken $analyticsToken, private readonly Http\ClientInterface $httpClient, private readonly ScopeConfigInterface $config, private readonly LoggerInterface $logger, private readonly FlagManager $flagManager, private readonly ResponseResolver $responseResolver)
    {
    }

    /**
     * Executes update request to MBI api in case store url was changed
     */
    public function execute(): bool
    {
        $result = false;
        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                Request::METHOD_PUT,
                $this->config->getValue($this->updateUrlPath),
                [
                    'url' => $this->flagManager
                        ->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE),
                    'new-url' => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                    'access-token' => $this->analyticsToken->getToken(),
                ]
            );
            $result = $this->responseResolver->getResult($response);
            if (!$result) {
                $this->logger->warning(
                    sprintf(
                        'Update of the subscription for MBI service has been failed: %s. Content-Type: %s',
                        !empty($response->getBody()) ? $response->getBody() : 'Response body is empty',
                        $response->getHeaders()->has('Content-Type') ?
                            $response->getHeaders()->get('Content-Type')->getFieldValue() :
                            ''
                    )
                );
            }
        }

        return (bool)$result;
    }
}
