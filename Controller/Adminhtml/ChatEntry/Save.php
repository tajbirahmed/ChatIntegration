<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Controller\Adminhtml\ChatEntry;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use BS23\ChatIntegration\Model\ChatEntryFactory;
use BS23\ChatIntegration\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Admin controller: Save (create or update) a chat entry.
 *
 * Handles UI component form POST. Icon files are pre-uploaded via the Upload
 * controller to the tmp directory; this controller moves them to the final
 * media path on save using ImageUploader::moveFileFromTmp().
 */
class Save extends Action
{
    public const ADMIN_RESOURCE = 'BS23_ChatIntegration::chat_entry';

    public function __construct(
        Context $context,
        private readonly HttpRequest $httpRequest,
        private readonly ChatEntryRepositoryInterface $chatEntryRepository,
        private readonly ChatEntryFactory $chatEntryFactory,
        private readonly ImageUploader $imageUploader,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data           = $this->httpRequest->getPostValue();

        if (empty($data)) {
            return $resultRedirect->setPath('*/*/index');
        }

        $id           = isset($data['entry_id']) && $data['entry_id'] !== '' ? (int) $data['entry_id'] : null;
        $redirectPath = $id ? '*/*/edit' : '*/*/new';
        $redirectArgs = $id ? ['entry_id' => $id] : [];

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

            // ── Store view scope ──────────────────────────────────────────
            $storeIds = isset($data['store_ids'])
                ? array_map('intval', (array) $data['store_ids'])
                : [0]; // 0 = All Store Views
            $entry->setStoreIds($storeIds);

            // ── Icon background color ─────────────────────────────────────
            $iconBgColor = trim((string) ($data['icon_bg_color'] ?? ''));
            $entry->setIconBgEnabled((bool) ($data['icon_bg_enabled'] ?? false));
            $entry->setIconBgColor(
                preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $iconBgColor)
                    ? $iconBgColor
                    : null
            );

            // ── Handle icon from UI fileUploader ─────────────────────────
            $iconName = $this->processIconData($data['icon'] ?? null);
            if ($iconName !== null) {
                $entry->setIcon($iconName);
            }

            $this->chatEntryRepository->save($entry);
            $this->messageManager->addSuccessMessage(__('Chat entry has been saved.'));

            $redirectPath = $this->getRequest()->getParam('back') ? '*/*/edit' : '*/*/index';
            $redirectArgs = $this->getRequest()->getParam('back') ? ['entry_id' => $entry->getId()] : [];
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical('BS23 ChatIntegration Save error: ' . $e->getMessage());
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the chat entry.'));
        }

        return $resultRedirect->setPath($redirectPath, $redirectArgs);
    }

    /**
     * Resolve icon data submitted by the UI fileUploader widget.
     *
     * The widget submits an array (or JSON-encoded array) of file descriptors.
     * If the file is newly uploaded it lives in the tmp dir and must be moved
     * to the permanent media path. If it is an unchanged existing icon the
     * move will fail and we simply return the stored filename as-is.
     *
     * Returns null when no icon data is present (no change to existing icon).
     *
     * @throws LocalizedException
     */
    private function processIconData(mixed $iconRaw): ?string
    {
        // UI fileUploader may submit JSON string or a PHP array
        if (is_string($iconRaw) && $iconRaw !== '') {
            $iconRaw = json_decode($iconRaw, true) ?? [];
        }

        if (!is_array($iconRaw) || empty($iconRaw[0]['file'])) {
            return null;
        }

        $fileName = (string) $iconRaw[0]['file'];

        try {
            // New file: move from tmp to permanent media directory
            return $this->imageUploader->moveFileFromTmp($fileName);
        } catch (LocalizedException) {
            // File already in permanent location (existing icon, no change)
            return $fileName;
        }
    }
}
