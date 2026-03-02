<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Uploader;
use Magento\Framework\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles icon image upload for Chat Entries.
 *
 * Accepts svg, png, jpg/jpeg, webp.
 * Validates MIME type in addition to extension to prevent spoofing.
 * File size limit is enforced by PHP/server – admin-level size limit is
 * informational and enforced in the UI form validator.
 */
class ImageUploader
{
    private const ALLOWED_MIME_TYPES = [
        'image/svg+xml',
        'image/png',
        'image/jpeg',
        'image/webp',
    ];

    private readonly WriteInterface $mediaDirectory;

    public function __construct(
        private readonly Database $coreFileStorageDatabase,
        private readonly Filesystem $filesystem,
        private readonly UploaderFactory $uploaderFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger,
        private readonly string $baseTmpPath,
        private readonly string $basePath,
        private readonly array $allowedExtensions
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Move an uploaded file to the tmp media directory and return metadata.
     *
     * @param string $fileId  HTML input name attribute
     * @return array{name: string, type: string, tmp_name: string, url: string, size: int}
     * @throws LocalizedException
     */
    public function saveFileToTmpDir(string $fileId): array
    {
        /** @var Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->allowedExtensions);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        // Validate MIME type to prevent extension spoofing
        $fileInfo = $uploader->validateFile();
        if (!in_array($fileInfo['type'], self::ALLOWED_MIME_TYPES, true)) {
            throw new LocalizedException(
                __('File type "%1" is not allowed. Accepted formats: SVG, PNG, JPG, WebP.', $fileInfo['type'])
            );
        }

        $result = $uploader->save($this->mediaDirectory->getAbsolutePath($this->baseTmpPath));

        if (!$result) {
            throw new LocalizedException(__('File cannot be saved to the temporary directory.'));
        }

        $result['tmp_name'] = $this->baseTmpPath . '/' . $result['file'];
        $result['url']      = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                              . $this->baseTmpPath . '/' . $result['file'];
        $result['name']     = $result['file'];

        $this->coreFileStorageDatabase->saveFile($result['tmp_name']);

        return $result;
    }

    /**
     * Move the previously-uploaded temp file to the permanent media path.
     *
     * @throws LocalizedException
     */
    public function moveFileFromTmp(string $imageName): string
    {
        $baseImagePath    = $this->getFilePath($this->basePath, $imageName);
        $baseTmpImagePath = $this->getFilePath($this->baseTmpPath, $imageName);

        try {
            $this->coreFileStorageDatabase->copyFile($baseTmpImagePath, $baseImagePath);
            $this->mediaDirectory->renameFile($baseTmpImagePath, $baseImagePath);
        } catch (\Exception $e) {
            $this->logger->error(
                'BS23 ChatIntegration: failed to move icon file.',
                ['exception' => $e->getMessage(), 'file' => $imageName]
            );
            throw new LocalizedException(
                __('Something went wrong while saving the icon file: %1', $e->getMessage()),
                $e
            );
        }

        return $imageName;
    }

    /**
     * Build absolute media-relative path.
     */
    private function getFilePath(string $path, string $imageName): string
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }
}
