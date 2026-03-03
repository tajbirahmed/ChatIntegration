<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Admin controller: Render the Chat Entry add/edit page.
 *
 * Data is supplied to the UI component form by the DataProvider,
 * not via DataPersistor, so no model loading is needed here beyond
 * resolving the page title.
 */
class Edit extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly ChatEntryRepositoryInterface $chatEntryRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): Page|Redirect
    {
        $id         = (int) $this->getRequest()->getParam('entry_id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('BS23_ChatIntegration::chat_entry');

        if ($id) {
            try {
                $entry = $this->chatEntryRepository->getById($id);
                $resultPage->getConfig()->getTitle()->prepend(
                    __('Edit Chat Entry: %1', $entry->getName())
                );
            } catch (NoSuchEntityException) {
                $this->messageManager->addErrorMessage(__('This chat entry no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Chat Entry'));
        }

        return $resultPage;
    }
}
