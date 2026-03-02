<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Admin controller: Mass-delete selected chat entries.
 */
class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly ChatEntryRepositoryInterface $chatEntryRepository,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted    = 0;
        $failed     = 0;

        foreach ($collection->getItems() as $entry) {
            try {
                $this->chatEntryRepository->delete($entry);
                $deleted++;
            } catch (\Exception $e) {
                $failed++;
                $this->logger->error(
                    'BS23 ChatIntegration: failed to delete entry ID ' . $entry->getId(),
                    ['exception' => $e->getMessage()]
                );
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 chat entry(s) have been deleted.', $deleted)
        );

        if ($failed > 0) {
            $this->messageManager->addErrorMessage(
                __('%1 entry(s) could not be deleted. See logs for details.', $failed)
            );
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
