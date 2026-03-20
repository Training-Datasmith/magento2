<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\Analytics_Token;
use Magento\Analytics\Model\Connector\Http\Response_Resolver;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Store\Model\Store;
/**
 * Command notifies MBI about that data collection was finished.
 */
class Notify_Data_Changed_Command implements Command_Interface
{
    private string $notify_data_changed_url_path = 'analytics/url/notify_data_changed';
    /**
     * NotifyDataChangedCommand constructor.
     */
    public function __construct(private readonly Analytics_Token $analytics_token, private readonly Http\Client_Interface $http_client, private readonly Scope_Config_Interface $config, private readonly Response_Resolver $response_resolver)
    {
    }
    /**
     * Notify MBI about that data collection was finished
     */
    public function execute(): bool
    {
        $result = false;
        if ($this->analytics_token->is_token_exist()) {
            $response = $this->http_client->request(Request::METHOD_POST, $this->config->get_value($this->notify_data_changed_url_path), ['access-token' => $this->analytics_token->get_token(), 'url' => $this->config->get_value(Store::XML_PATH_SECURE_BASE_URL)]);
            $result = $this->response_resolver->get_result($response);
        }
        return (bool) $result;
    }
}