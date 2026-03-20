<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Config\Reinitable_Config_Interface;
/**
 * @inheritdoc
 * @deprecated 101.0.0
 */
class Reinitable_Config extends Mutable_Scope_Config implements Reinitable_Config_Interface
{
    /**
     * {@inheritdoc}
     */
    public function reinit()
    {
        $this->clean();
        return $this;
    }
}