<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use BS23\ChatIntegration\Model\ChatEntryFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\File\UploaderFactory;
use Psr\Log\LoggerInterface;

/**
 * Admin controller: Save (create or update) a chat entry.
 *
 * Handles classic multipart/form-data POST with an optional file upload.
 */
class Save extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    private const ALLOWED_EXTENSIONS = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
    private const MEDIA_SUBDIR       = 'bs23/chatentry';

    public function __construct(
        Context $context,
        private readonly ChatEntryRepositoryInterface $chatEntryRepository,
        private readonly ChatEntryFactory $chatEntryFactory,
        private readonly UploaderFactory $uploaderFactory,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->getRequest()->getPostValue();

        if (empty($data)) {
            return $resultRedirect->setPath('*/*/index');
        }

        $id = isset($data['entry_id']) && $data['entry_id'] !== '' ? (int) $data['entry_id'] : null;

        try {
            $entry = $id
                ? $this->chatEntryRepository->getById($id)
                : $this->chatEntryFactory->create();

            // ── Validate URL ─────────────────────────────────────────────
            $url = trim((string) ($data['url'] ?? ''));
            if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
                throw new LocalizedException(__('Please enter a valid deep link URL.'));
            }

            $entry->setName(trim((string) ($data['name'] ?? '')));
            $entry->setUrl($url);
            $entry->setSortOrder((int) ($data['sort_order'] ?? 0));
            $entry->setIsEnabled((bool) ($data['is_enabled'] ?? false));

            // ── Handle optional icon file upload ─────────────────────────
            $iconName = $this->uploadIcon($entry->getIcon());
            if ($iconName !== null) {
                $entry->setIcon($iconName);
            }

            $this->chatEntryRepository->save($entry);
            $this->messageManager->addSuccessMessage(__('Chat entry has been saved.'));

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['entry_id' => $entry->getId()]);
            }

            return $resultRedirect->setPath('*/*/index');

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical('BS23 ChatIntegration Save error: ' . $e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the chat entry.'));
        }

        return $id
            ? $resultRedirect->setPath('*/*/edit', ['entry_id' => $id])
            : $resultRedirect->setPath('*/*/new');
    }

    /**
     * Upload icon_file if present; returns the saved filename or null if no upload.
     *
     * @throws LocalizedException
     */
    private function uploadIcon(?string $currentIcon): ?string
    {
        $files = $this->getRequest()->getFiles();
        $file  = $files['icon_file'] ?? null;

        // No file selected or empty upload
        if (!$file || empty($file['name']) || ($file['error'] ?? \UPLOAD_ERR_NO_FILE) === \UPLOAD_ERR_NO_FILE) {
            return null;
        }

        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'icon_file']);
            $uploader->setAllowedExtensions(self::ALLOWED_EXTENSIONS);
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            $result   = $uploader->save($mediaDir->getAbsolutePath(self::MEDIA_SUBDIR));

            return $result['file'] ?? null;
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Icon upload failed: %1', $e->getMessage()),
                $e
            );
        }
    }
}
