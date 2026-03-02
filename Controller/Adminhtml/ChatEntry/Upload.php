<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Admin controller: AJAX icon image upload endpoint.
 *
 * Saves the uploaded file to the tmp media directory and returns
 * file metadata as JSON for the Magento UI file uploader widget.
 */
class Upload extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly JsonFactory $resultJsonFactory,
        private readonly ImageUploader $imageUploader
    ) {
        parent::__construct($context);
    }

    public function execute(): Json
    {
        try {
            $result = $this->imageUploader->saveFileToTmpDir('icon');
        } catch (\Exception $e) {
            $result = [
                'error'     => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
