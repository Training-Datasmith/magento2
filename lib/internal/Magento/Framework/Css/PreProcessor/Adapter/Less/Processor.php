<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\Adapter\Less;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\State;
use Magento\Framework\Css\Pre_Processor\File\Temporary;
use Magento\Framework\Phrase;
use Magento\Framework\View\Asset\Content_Processor_Exception;
use Magento\Framework\View\Asset\Content_Processor_Interface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Source;
use Psr\Log\Logger_Interface;
/**
 * Class Processor
 *
 * Process LESS files into CSS
 */
class Processor implements Content_Processor_Interface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var State
     */
    private $app_state;
    /**
     * @var Source
     */
    private $asset_source;
    /**
     * @var Temporary
     */
    private $temporary_file;
    /**
     * @var DirectoryList
     */
    private Directory_List $directory_list;
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param State $appState
     * @param Source $assetSource
     * @param Temporary $temporaryFile
     * @param ?DirectoryList $directoryList
     */
    public function __construct(Logger_Interface $logger, State $app_state, Source $asset_source, Temporary $temporary_file, ?Directory_List $directory_list = null)
    {
        $this->logger = $logger;
        $this->app_state = $app_state;
        $this->asset_source = $asset_source;
        $this->temporary_file = $temporary_file;
        $this->directory_list = $directory_list ?: Object_Manager::get_instance()->get(Directory_List::class);
    }
    /**
     * @inheritdoc
     */
    public function process_content(File $asset)
    {
        $path = $asset->get_path();
        try {
            $mode = $this->app_state->get_mode();
            $source_map_base_path = sprintf('%s/pub/', $this->directory_list->get_path(Directory_List::TEMPLATE_MINIFICATION_DIR));
            $parser = new \Less_Parser(['relativeUrls' => false, 'compress' => $mode !== State::MODE_DEVELOPER, 'sourceMap' => $mode === State::MODE_DEVELOPER, 'sourceMapRootpath' => '/', 'sourceMapBasepath' => $source_map_base_path]);
            $content = $this->asset_source->get_content($asset);
            if (trim($content) === '') {
                throw new Content_Processor_Exception(new Phrase('Compilation from source: LESS file is empty: ' . $path));
            }
            $tmp_file_path = $this->temporary_file->create_file($path, $content);
            gc_disable();
            $parser->parse_file($tmp_file_path, '');
            $content = $parser->get_css();
            gc_enable();
            if (trim($content) === '') {
                throw new Content_Processor_Exception(new Phrase('Compilation from source: LESS file is empty: ' . $path));
            } else {
                return $content;
            }
        } catch (\Exception $e) {
            throw new Content_Processor_Exception(new Phrase($e->get_message()));
        }
    }
}