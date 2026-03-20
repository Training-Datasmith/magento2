<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Authorization\Model;

use Magento\Framework\Object_Manager\Helper\Composite as CompositeHelper;
use Magento\Framework\Object_Manager\Reset_After_Request_Interface;
/**
 * User context.
 *
 * This class is not implementing standard composite pattern and will not invoke all of its children.
 * Instead, it will try to find the first suitable child and return its result.
 *
 * @api
 * @since 100.0.2
 */
class Composite_User_Context implements \Magento\Authorization\Model\User_Context_Interface, Reset_After_Request_Interface
{
    /**
     * @var UserContextInterface[]
     */
    protected $user_contexts = [];
    /**
     * @var UserContextInterface|bool
     */
    protected $chosen_user_context;
    /**
     * Register user contexts.
     *
     * @param UserContextInterface[] $userContexts
     */
    public function __construct(Composite_Helper $composite_helper, $user_contexts = [])
    {
        $user_contexts = $composite_helper->filter_and_sort_declared_components($user_contexts);
        foreach ($user_contexts as $user_context) {
            $this->add($user_context['type']);
        }
    }
    /**
     * Add user context.
     */
    protected function add(User_Context_Interface $user_context): static
    {
        $this->user_contexts[] = $user_context;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function get_user_id(): ?int
    {
        return $this->get_user_context() ? (int) $this->get_user_context()->get_user_id() : null;
    }
    /**
     * @inheritDoc
     */
    public function get_user_type()
    {
        return $this->get_user_context() ? $this->get_user_context()->get_user_type() : null;
    }
    /**
     * Retrieve user context
     *
     * @return UserContextInterface|bool False if none of the registered user contexts can identify user type
     */
    protected function get_user_context()
    {
        if (!$this->chosen_user_context) {
            /** @var UserContextInterface $userContext */
            foreach ($this->user_contexts as $user_context) {
                if ($user_context->get_user_type() && $user_context->get_user_id() !== null) {
                    $this->chosen_user_context = $user_context;
                    break;
                }
            }
            if ($this->chosen_user_context === null) {
                $this->chosen_user_context = false;
            }
        }
        return $this->chosen_user_context;
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        $this->chosen_user_context = null;
    }
}