<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Http;

use Laminas\Http\Response;
/**
 * Extract result from http response. Call response handler by status.
 */
class Response_Resolver
{
    /**
     * @param ResponseHandlerInterface[] $responseHandlers
     */
    public function __construct(private readonly Converter_Interface $converter, private array $response_handlers = [])
    {
    }
    /**
     * Get result from $response.
     *
     * @return bool|string
     */
    public function get_result(Response $response)
    {
        $result = false;
        $converter_media_type = $this->converter->get_content_media_type();
        /** Content-Type header may not only contain media-type declaration */
        $response_body = $response->get_body();
        $content_type = $response->get_headers()->has('Content-Type') ? $response->get_headers()->get('Content-Type')->get_field_value() : '';
        if ($response_body && is_int(strripos((string) $content_type, $converter_media_type))) {
            $response_body = $this->converter->from_body($response_body);
        } else {
            $response_body = [];
        }
        if (array_key_exists($response->get_status_code(), $this->response_handlers)) {
            return $this->response_handlers[$response->get_status_code()]->handle_response($response_body);
        }
        return $result;
    }
}