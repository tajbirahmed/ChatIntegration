<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Model\ResourceModel\ChatEntry\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Admin controller: Mass-enable selected chat entries.
 */
class MassEnable extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count      = 0;

        foreach ($collection->getItems() as $entry) {
            $entry->setIsEnabled(true);
            $entry->save();
            $count++;
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 chat entry(s) have been enabled.', $count)
        );

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
