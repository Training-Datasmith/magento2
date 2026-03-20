<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Backup;

/**
 * Class to work system backup that excludes media folder
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Nomedia extends \Magento\Framework\Backup\Media
{
    /**
     * Overlap getType
     *
     * @return string
     * @see BackupInterface::getType()
     */
    public function get_type()
    {
        return 'nomedia';
    }
    /**
     * Add media folder to ignore list
     *
     * @return $this
     */
    protected function _prepare_ignore_list()
    {
        $root_dir = $this->get_root_dir();
        $this->add_ignore_paths([$root_dir . '/media', $root_dir . '/pub/media']);
        return $this;
    }
}