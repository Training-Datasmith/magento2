<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Authorization\Model;

use Magento\Framework\ObjectManager\Helper\Composite as CompositeHelper;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * User context.
 *
 * This class is not implementing standard composite pattern and will not invoke all of its children.
 * Instead, it will try to find the first suitable child and return its result.
 *
 * @api
 * @since 100.0.2
 */
class CompositeUserContext implements \Magento\Authorization\Model\UserContextInterface, ResetAfterRequestInterface
{
    /**
     * @var UserContextInterface[]
     */
    protected $userContexts = [];

    /**
     * @var UserContextInterface|bool
     */
    protected $chosenUserContext;

    /**
     * Register user contexts.
     *
     * @param UserContextInterface[] $userContexts
     */
    public function __construct(CompositeHelper $compositeHelper, $userContexts = [])
    {
        $userContexts = $compositeHelper->filterAndSortDeclaredComponents($userContexts);
        foreach ($userContexts as $userContext) {
            $this->add($userContext['type']);
        }
    }

    /**
     * Add user context.
     */
    protected function add(UserContextInterface $userContext): static
    {
        $this->userContexts[] = $userContext;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): ?int
    {
        return $this->getUserContext() ? ((int) $this->getUserContext()->getUserId()) : null;
    }

    /**
     * @inheritDoc
     */
    public function getUserType()
    {
        return $this->getUserContext() ? $this->getUserContext()->getUserType() : null;
    }

    /**
     * Retrieve user context
     *
     * @return UserContextInterface|bool False if none of the registered user contexts can identify user type
     */
    protected function getUserContext()
    {
        if (!$this->chosenUserContext) {
            /** @var UserContextInterface $userContext */
            foreach ($this->userContexts as $userContext) {
                if ($userContext->getUserType() && $userContext->getUserId() !== null) {
                    $this->chosenUserContext = $userContext;
                    break;
                }
            }
            if ($this->chosenUserContext === null) {
                $this->chosenUserContext = false;
            }
        }
        return $this->chosenUserContext;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->chosenUserContext = null;
    }
}
