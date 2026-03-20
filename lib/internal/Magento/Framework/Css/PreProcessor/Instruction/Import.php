<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\Instruction;

use Magento\Framework\Css\Pre_Processor\File_Generator\Related_Generator;
use Magento\Framework\View\Asset\Local_Interface;
use Magento\Framework\View\Asset\Notation_Resolver;
use Magento\Framework\View\Asset\Pre_Processor\Chain;
use Magento\Framework\View\Asset\Pre_Processor_Interface;
/**
 * 'import' instruction preprocessor
 */
class Import implements Pre_Processor_Interface
{
    /**
     * Pattern of 'import' instruction
     */
    public const REPLACE_PATTERN = '#@import[\s]*' . '(?P<start>[\(\),\w\s]*?[\'\"][\s]*)' . '(?P<path>[^\)\'\"]*?)' . '(?P<end>[\s]*[\'\"][\s\w]*[\)]?)[\s]*;#';
    /**
     * @var \Magento\Framework\View\Asset\NotationResolver\Module
     */
    private $notation_resolver;
    /**
     * @var array
     */
    protected $related_files = [];
    /**
     * @var RelatedGenerator
     */
    private $related_file_generator;
    /**
     * Constructor
     *
     * @param NotationResolver\Module $notationResolver
     * @param RelatedGenerator $relatedFileGenerator
     */
    public function __construct(Notation_Resolver\Module $notation_resolver, Related_Generator $related_file_generator)
    {
        $this->notation_resolver = $notation_resolver;
        $this->related_file_generator = $related_file_generator;
    }
    /**
     * @inheritdoc
     */
    public function process(Chain $chain)
    {
        $asset = $chain->get_asset();
        $content_type = $chain->get_content_type();
        $replace_callback = function ($match_content) use ($asset, $content_type) {
            return $this->replace($match_content, $asset, $content_type);
        };
        $content = $this->remove_comments($chain->get_content());
        $processed_content = preg_replace_callback(self::REPLACE_PATTERN, $replace_callback, $content);
        $this->related_file_generator->generate($this);
        if ($processed_content !== $content) {
            $chain->set_content($processed_content);
        }
    }
    /**
     * Returns the content without commented lines
     *
     * @param string $content
     * @return string
     */
    private function remove_comments($content)
    {
        return preg_replace("#(^\\s*//.*\$)|((^\\s*/\\*(?s).*?(\\*/)(?!\\*/))\$)#m", '', $content);
    }
    /**
     * Retrieve information on all related files, processed so far
     *
     * BUG: this information about related files is not supposed to be in the state of this object.
     * This class is meant to be a service (shareable instance) without such a transient state.
     * The list of related files needs to be accumulated for the preprocessor,
     * because it uses a 3rd-party library, which requires the files to physically reside in the base same directory.
     *
     * @return array
     */
    public function get_related_files()
    {
        return $this->related_files;
    }
    /**
     * Clear the record of related files, processed so far
     *
     * @return void
     */
    public function reset_related_files()
    {
        $this->related_files = [];
    }
    /**
     * Add related file to the record of processed files
     *
     * @param string $matchedFileId
     * @param LocalInterface $asset
     * @return void
     */
    protected function record_related_file($matched_file_id, Local_Interface $asset)
    {
        $this->related_files[] = [$matched_file_id, $asset];
    }
    /**
     * Return replacement of an original @import directive
     *
     * @param array $matchedContent
     * @param LocalInterface $asset
     * @param string $contentType
     * @return string
     */
    protected function replace(array $matched_content, Local_Interface $asset, $content_type)
    {
        $matched_file_id = $this->fix_file_extension($matched_content['path'], $content_type);
        $start = $matched_content['start'];
        $end = $matched_content['end'];
        if ($start && strpos(trim($start), 'url') !== 0) {
            $this->record_related_file($matched_file_id, $asset);
        }
        $resolved_path = $this->notation_resolver->convert_module_notation_to_path($asset, $matched_file_id);
        return "@import {$start}{$resolved_path}{$end};";
    }
    /**
     * Resolve extension of imported asset according to exact format
     *
     * @param string $fileId
     * @param string $contentType
     * @return string
     * @link http://lesscss.org/features/#import-directives-feature-file-extensions
     */
    protected function fix_file_extension($file_id, $content_type)
    {
        if (!pathinfo($file_id, PATHINFO_EXTENSION)) {
            $file_id .= '.' . $content_type;
        }
        return $file_id;
    }
}