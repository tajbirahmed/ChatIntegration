<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry\Edit\Button;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Base button class for Chat Entry edit form buttons.
 *
 * Provides shared URL builder and request access used by all button classes.
 */
class GenericButton
{
    public function __construct(
        protected readonly Context $context,
        protected readonly ChatEntryRepositoryInterface $chatEntryRepository
    ) {
    }

    /**
     * Return the current entry_id from the request, or null for new entries.
     */
    public function getEntryId(): ?int
    {
        try {
            $id = (int) $this->context->getRequest()->getParam('entry_id');
            if ($id) {
                return (int) $this->chatEntryRepository->getById($id)->getId();
            }
        } catch (NoSuchEntityException) {
        }

        return null;
    }

    /**
     * Generate a URL by route and parameters.
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
