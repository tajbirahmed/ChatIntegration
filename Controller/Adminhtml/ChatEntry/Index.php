<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Admin controller: Chat Entries grid listing.
 */
class Index extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('BS23_ChatIntegration::chat_entry');
        $resultPage->getConfig()->getTitle()->prepend(__('Chat Entries'));

        return $resultPage;
    }
}
