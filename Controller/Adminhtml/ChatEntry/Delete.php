<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Admin controller: Delete a single chat entry.
 */
class Delete extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly ChatEntryRepositoryInterface $chatEntryRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id             = (int) $this->getRequest()->getParam('entry_id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('Invalid chat entry ID.'));

            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $this->chatEntryRepository->deleteById($id);
            $this->messageManager->addSuccessMessage(__('Chat entry has been deleted.'));
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This chat entry no longer exists.'));
        } catch (\Exception) {
            $this->messageManager->addErrorMessage(__('An error occurred while deleting the chat entry.'));
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
