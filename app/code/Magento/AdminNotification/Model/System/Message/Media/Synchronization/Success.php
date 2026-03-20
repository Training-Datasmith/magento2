<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model\System\Message\Media\Synchronization;

/**
 * Media synchronization success message class.
 *
 * @api
 * @since 100.0.2
 */
class Success extends \Magento\Admin_Notification\Model\System\Message\Media\Abstract_Synchronization
{
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity = 'MEDIA_SYNCHRONIZATION_SUCCESS';
    /**
     * Check whether
     */
    protected function _should_be_displayed(): bool
    {
        $state = $this->_sync_flag->get_state();
        $data = $this->_sync_flag->get_flag_data();
        $has_errors = !empty($data['has_errors']);
        return !$has_errors && \Magento\Media_Storage\Model\File\Storage\Flag::STATE_FINISHED == $state;
    }
    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function get_text()
    {
        return __('Synchronization of media storages has been completed.');
    }
}