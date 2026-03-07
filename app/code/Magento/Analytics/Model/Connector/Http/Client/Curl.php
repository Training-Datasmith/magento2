<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector\Http\Client;

use Laminas\Http\Response;
use Magento\Analytics\Model\Connector\Http\ConverterInterface;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\ResponseFactory;
use Psr\Log\LoggerInterface;

/**
 * A CURL HTTP client.
 *
 * Sends requests via a CURL adapter.
 */
class Curl implements \Magento\Analytics\Model\Connector\Http\ClientInterface
{
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    public function __construct(
        CurlFactory $curlFactory,
        private readonly ResponseFactory $responseFactory,
        private readonly ConverterInterface $converter,
        private readonly LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
    }

    /**
     * @inheritdoc
     */
    public function request($method, $url, array $body = [], array $headers = [], $version = '1.1')
    {
        $response = new Response();
        $response->setCustomStatusCode(Response::STATUS_CODE_CUSTOM);

        try {
            $curl = $this->curlFactory->create();
            $headers = $this->applyContentTypeHeaderFromConverter($headers);

            $curl->write($method, $url, $version, $headers, $this->converter->toBody($body));

            $result = $curl->read();

            if ($curl->getErrno()) {
                $this->logger->critical(
                    new \Exception(
                        sprintf(
                            'MBI service CURL connection error #%s: %s',
                            $curl->getErrno(),
                            $curl->getError()
                        )
                    )
                );

                return $response;
            }

            $response = $this->responseFactory->create($result);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $response;
    }

    /**
     * Apply content type header from converter
     *
     *
     */
    private function applyContentTypeHeaderFromConverter(array $headers): array
    {
        $contentTypeHeaderKey = array_search($this->converter->getContentTypeHeader(), $headers);
        if ($contentTypeHeaderKey === false) {
            $headers[] = $this->converter->getContentTypeHeader();
        }

        return $headers;
    }
}
