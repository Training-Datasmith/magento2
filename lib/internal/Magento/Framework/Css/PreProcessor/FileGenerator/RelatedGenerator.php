<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Css\Pre_Processor\File_Generator;

use Magento\Framework\Css\Pre_Processor\File\Temporary;
use Magento\Framework\Css\Pre_Processor\Instruction\Import;
use Magento\Framework\View\Asset\Local_Interface;
use Magento\Framework\View\Asset\Repository;
/**
 * Class RelatedGenerator
 */
class Related_Generator
{
    /**
     * @var Repository
     */
    private $asset_repository;
    /**
     * @var Temporary
     */
    private $temporary_file;
    /**
     * @param Repository $assetRepository
     * @param Temporary $temporaryFile
     */
    public function __construct(Repository $asset_repository, Temporary $temporary_file)
    {
        $this->asset_repository = $asset_repository;
        $this->temporary_file = $temporary_file;
    }
    /**
     * Create all asset files, referenced from already processed ones
     *
     * @param Import $importGenerator
     *
     * @return void
     */
    public function generate(Import $import_generator)
    {
        do {
            $related_files = $import_generator->get_related_files();
            $import_generator->reset_related_files();
            foreach ($related_files as $related_file_info) {
                list($related_file_id, $asset) = $related_file_info;
                $this->generate_related_file($related_file_id, $asset);
            }
        } while ($related_files);
    }
    /**
     * Create file, referenced relatively to an asset
     *
     * @param string $relatedFileId
     * @param LocalInterface $asset
     * @return \Magento\Framework\View\Asset\File
     */
    protected function generate_related_file($related_file_id, Local_Interface $asset)
    {
        $related_asset = $this->asset_repository->create_related($related_file_id, $asset);
        $this->temporary_file->create_file($related_asset->get_path(), $related_asset->get_content());
        return $related_asset;
    }
}