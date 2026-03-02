<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use BS23\ChatIntegration\Model\ChatEntryFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Admin controller: Edit an existing chat entry.
 */
class Edit extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly ChatEntryRepositoryInterface $chatEntryRepository,
        private readonly ChatEntryFactory $chatEntryFactory,
        private readonly Registry $coreRegistry
    ) {
        parent::__construct($context);
    }

    public function execute(): Page|Redirect
    {
        $id = (int) $this->getRequest()->getParam('entry_id');

        if ($id) {
            try {
                $entry = $this->chatEntryRepository->getById($id);
            } catch (NoSuchEntityException) {
                $this->messageManager->addErrorMessage(__('This chat entry no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        } else {
            $entry = $this->chatEntryFactory->create();
        }

        // Register so the form block can read it without ObjectManager
        $this->coreRegistry->register('bs23_chat_entry', $entry);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('BS23_ChatIntegration::chat_entry');
        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Chat Entry: %1', $entry->getName()) : __('New Chat Entry')
        );

        return $resultPage;
    }
}
