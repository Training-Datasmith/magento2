<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Config\Backend\Baseurl\Subscription_Update_Handler;
use Magento\Analytics\Model\Connector\Http\Response_Resolver;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Framework\Flag_Manager;
use Magento\Store\Model\Store;
use Psr\Log\Logger_Interface;
/**
 * Command executes in case change store url
 */
class Update_Command implements Command_Interface
{
    private string $update_url_path = 'analytics/url/update';
    public function __construct(private readonly Analytics_Token $analytics_token, private readonly Http\Client_Interface $http_client, private readonly Scope_Config_Interface $config, private readonly Logger_Interface $logger, private readonly Flag_Manager $flag_manager, private readonly Response_Resolver $response_resolver)
    {
    }
    /**
     * Executes update request to MBI api in case store url was changed
     */
    public function execute(): bool
    {
        $result = false;
        if ($this->analytics_token->is_token_exist()) {
            $response = $this->http_client->request(Request::METHOD_PUT, $this->config->get_value($this->update_url_path), ['url' => $this->flag_manager->get_flag_data(Subscription_Update_Handler::PREVIOUS_BASE_URL_FLAG_CODE), 'new-url' => $this->config->get_value(Store::XML_PATH_SECURE_BASE_URL), 'access-token' => $this->analytics_token->get_token()]);
            $result = $this->response_resolver->get_result($response);
            if (!$result) {
                $this->logger->warning(sprintf('Update of the subscription for MBI service has been failed: %s. Content-Type: %s', !empty($response->get_body()) ? $response->get_body() : 'Response body is empty', $response->get_headers()->has('Content-Type') ? $response->get_headers()->get('Content-Type')->get_field_value() : ''));
            }
        }
        return (bool) $result;
    }
}