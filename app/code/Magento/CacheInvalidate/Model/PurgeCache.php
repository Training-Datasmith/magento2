<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Cache_Invalidate\Model;

use Exception;
use Generator;
use Laminas\Http\Client\Adapter\Socket;
use Laminas\Uri\Uri;
use Magento\Framework\Cache\Invalidate_Logger;
use Magento\Page_Cache\Model\Cache\Server;
/**
 * Invalidate external HTTP cache(s) based on tag pattern
 */
class Purge_Cache
{
    public const HEADER_X_MAGENTO_TAGS_PATTERN = 'X-Magento-Tags-Pattern';
    /**
     * @var Server
     */
    protected $cache_server;
    /**
     * @var SocketFactory
     */
    protected $socket_adapter_factory;
    /**
     * @var InvalidateLogger
     */
    private $logger;
    /**
     * Batch size of the purge request.
     *
     * Based on default Varnish 6 http_req_hdr_len size minus a 512 bytes margin for method,
     * header name, line feeds etc.
     *
     * @see https://varnish-cache.org/docs/6.0/reference/varnishd.html
     *
     * @var int
     */
    private $max_header_size;
    /**
     * Constructor
     *
     * @param Server $cacheServer
     * @param SocketFactory $socketAdapterFactory
     * @param InvalidateLogger $logger
     * @param int $maxHeaderSize
     */
    public function __construct(Server $cache_server, Socket_Factory $socket_adapter_factory, Invalidate_Logger $logger, int $max_header_size = 7680)
    {
        $this->cache_server = $cache_server;
        $this->socket_adapter_factory = $socket_adapter_factory;
        $this->logger = $logger;
        $this->max_header_size = $max_header_size;
    }
    /**
     * Send curl purge request to invalidate cache by tags pattern
     *
     * @param array|string $tags
     * @return bool Return true if successful; otherwise return false
     */
    public function send_purge_request($tags)
    {
        if (is_string($tags)) {
            $tags = [$tags];
        }
        $successful = true;
        $socket_adapter = $this->socket_adapter_factory->create();
        $servers = $this->cache_server->get_uris();
        $socket_adapter->set_options(['timeout' => 10]);
        $formatted_tags_chunks = $this->chunk_tags($tags);
        foreach ($formatted_tags_chunks as $formatted_tags_chunk) {
            if (!$this->send_purge_request_to_servers($socket_adapter, $servers, $formatted_tags_chunk)) {
                $successful = false;
            }
        }
        return $successful;
    }
    /**
     * Split tags into batches to suit Varnish max. header size
     *
     * @param array $tags
     * @return Generator
     */
    private function chunk_tags(array $tags): Generator
    {
        $current_batch_size = 0;
        $formatted_tags_chunk = [];
        foreach ($tags as $formatted_tag) {
            // Check if (currentBatchSize + length of next tag + number of pipe delimiters) would exceed header size.
            if ($current_batch_size + strlen($formatted_tag ?: '') + count($formatted_tags_chunk) > $this->max_header_size) {
                yield implode('|', $formatted_tags_chunk);
                $formatted_tags_chunk = [];
                $current_batch_size = 0;
            }
            $current_batch_size += strlen($formatted_tag ?: '');
            $formatted_tags_chunk[] = $formatted_tag;
        }
        if (!empty($formatted_tags_chunk)) {
            yield implode('|', $formatted_tags_chunk);
        }
    }
    /**
     * Send curl purge request to servers to invalidate cache by tags pattern
     *
     * @param Socket $socketAdapter
     * @param Uri[] $servers
     * @param string $formattedTagsChunk
     * @return bool Return true if successful; otherwise return false
     */
    private function send_purge_request_to_servers(Socket $socket_adapter, array $servers, string $formatted_tags_chunk): bool
    {
        $headers = [self::HEADER_X_MAGENTO_TAGS_PATTERN => $formatted_tags_chunk];
        $unresponsive_server_error = [];
        foreach ($servers as $server) {
            $headers['Host'] = $server->get_host();
            try {
                $socket_adapter->connect($server->get_host(), $server->get_port());
                $socket_adapter->write('PURGE', $server, '1.1', $headers);
                $socket_adapter->read();
                $socket_adapter->close();
            } catch (Exception $e) {
                $unresponsive_server_error[] = 'Cache host: ' . $server->get_host() . ':' . $server->get_port() . 'resulted in error message: ' . $e->get_message();
            }
        }
        $error_count = count($unresponsive_server_error);
        if ($error_count > 0) {
            $logger_message = implode(' ', $unresponsive_server_error);
            if ($error_count == count($servers)) {
                $this->logger->critical('No cache server(s) could be purged ' . $logger_message, compact('servers', 'formattedTagsChunk'));
                return false;
            }
            $this->logger->warning('Unresponsive cache server(s) hit' . $logger_message, compact('servers', 'formattedTagsChunk'));
        }
        $this->logger->execute(compact('servers', 'formattedTagsChunk'));
        return true;
    }
}