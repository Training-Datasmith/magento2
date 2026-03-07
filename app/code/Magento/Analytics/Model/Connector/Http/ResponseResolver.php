<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model\Connector\Http;

use Laminas\Http\Response;

/**
 * Extract result from http response. Call response handler by status.
 */
class ResponseResolver
{
    /**
     * @param ResponseHandlerInterface[] $responseHandlers
     */
    public function __construct(private readonly ConverterInterface $converter, private array $responseHandlers = [])
    {
    }

    /**
     * Get result from $response.
     *
     * @return bool|string
     */
    public function getResult(Response $response)
    {
        $result = false;
        $converterMediaType = $this->converter->getContentMediaType();

        /** Content-Type header may not only contain media-type declaration */
        $responseBody = $response->getBody();
        $contentType = $response->getHeaders()->has('Content-Type') ?
            $response->getHeaders()->get('Content-Type')->getFieldValue() :
            '';
        if ($responseBody && is_int(strripos((string) $contentType, $converterMediaType))) {
            $responseBody = $this->converter->fromBody($responseBody);
        } else {
            $responseBody = [];
        }

        if (array_key_exists($response->getStatusCode(), $this->responseHandlers)) {
            return $this->responseHandlers[$response->getStatusCode()]->handleResponse($responseBody);
        }

        return $result;
    }
}
