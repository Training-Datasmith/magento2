<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

/**
 * Computes path info and query string from request
 */
class Path_Info
{
    /**
     * Get path info using from the request URI and base URL
     *
     * @param string $requestUri
     * @param string $baseUrl
     * @return string
     */
    public function get_path_info(string $request_uri, string $base_url): string
    {
        if ($request_uri === '/') {
            return '';
        }
        $request_uri = $this->remove_repeated_slashes($request_uri);
        $parsed_request_uri = explode('?', $request_uri, 2);
        $path_info = (string) substr(current($parsed_request_uri), (int) strlen($base_url));
        if ($this->is_no_route_uri($base_url, $path_info)) {
            $path_info = \Magento\Framework\App\Router\Base::NO_ROUTE;
        }
        return $path_info;
    }
    /**
     * Get query string using from the request URI
     *
     * @param string $requestUri
     * @return string
     */
    public function get_query_string(string $request_uri): string
    {
        $request_uri = $this->remove_repeated_slashes($request_uri);
        $parsed_request_uri = explode('?', $request_uri, 2);
        $query_string = !isset($parsed_request_uri[1]) ? '' : '?' . $parsed_request_uri[1];
        return $query_string;
    }
    /**
     * Remove repeated slashes from the start of the path.
     *
     * @param string $pathInfo
     * @return string
     */
    private function remove_repeated_slashes($path_info): string
    {
        $first_char = (string) substr($path_info, 0, 1);
        if ($first_char == '/') {
            $path_info = '/' . ltrim($path_info, '/');
        }
        return $path_info;
    }
    /**
     * Check is URI should be marked as no route, helps route to 404 URI like `index.phpadmin`.
     *
     * @param string $baseUrl
     * @param string $pathInfo
     * @return bool
     */
    private function is_no_route_uri($base_url, $path_info): bool
    {
        $first_char = (string) substr($path_info, 0, 1);
        return $base_url !== '' && !in_array($first_char, ['/', '']);
    }
}