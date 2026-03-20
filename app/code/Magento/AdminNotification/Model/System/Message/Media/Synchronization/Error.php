<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System\Message\Media\Synchronization;

/**
 * Media synchronization error message class.
 *
 * @api
 * @since 100.0.2
 */
class Error extends \Magento\Admin_Notification\Model\System\Message\Media\Abstract_Synchronization
{
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity = 'MEDIA_SYNCHRONIZATION_ERROR';
    /**
     * Check whether
     */
    protected function _should_be_displayed(): bool
    {
        $data = $this->_sync_flag->get_flag_data();
        return !empty($data['has_errors']);
    }
    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_text()
    {
        return __('We were unable to synchronize one or more media files. Please refer to the log file for details.');
    }
}