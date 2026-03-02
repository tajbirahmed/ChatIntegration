<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\ViewModel;

use BS23\ChatIntegration\Api\Data\ChatEntryInterface;
use BS23\ChatIntegration\Model\Config;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry\CollectionFactory;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Frontend ViewModel for the chat bubble block.
 *
 * Provides all configuration values and chat entries to the bubble template.
 * Uses Escaper for all output; no helpers, no ObjectManager.
 */
class ChatConfig implements ArgumentInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly CollectionFactory $collectionFactory,
        private readonly Escaper $escaper,
        private readonly StoreManagerInterface $storeManager
    ) {}

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function getPosition(): string
    {
        return $this->config->getPosition();
    }

    public function getLayoutStyle(): string
    {
        return $this->config->getLayoutStyle();
    }

    public function getBubbleSize(): int
    {
        return $this->config->getBubbleSize();
    }

    public function getIconSize(): int
    {
        return $this->config->getIconSize();
    }

    public function getIconSpacing(): int
    {
        return $this->config->getIconSpacing();
    }

    public function getBubbleBgColor(): string
    {
        return $this->config->getBubbleBgColor();
    }

    public function isHoverEffectEnabled(): bool
    {
        return $this->config->isHoverEffectEnabled();
    }

    /**
     * Return all enabled chat entries ordered by sort_order ASC.
     *
     * @return ChatEntryInterface[]
     */
    public function getChatEntries(): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('is_enabled', ['eq' => 1]);
        $collection->setOrder('sort_order', 'ASC');

        return array_values($collection->getItems());
    }

    /**
     * Build the full media URL for a stored icon path.
     */
    public function getIconUrl(string $iconPath): string
    {
        if ($iconPath === '') {
            return '';
        }

        try {
            $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $base . 'bs23/chatentry/' . ltrim($iconPath, '/');
        } catch (\Exception) {
            return '';
        }
    }

    /**
     * Full media URL for the admin-uploaded bubble icon; empty string if not set.
     */
    public function getBubbleIconUrl(): string
    {
        return $this->buildConfigImageUrl($this->config->getBubbleIconPath());
    }

    /**
     * Full media URL for the admin-uploaded close icon; empty string if not set.
     */
    public function getCloseIconUrl(): string
    {
        return $this->buildConfigImageUrl($this->config->getCloseIconPath());
    }

    private function buildConfigImageUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        try {
            $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $base . 'bs23/chatintegration/' . ltrim($path, '/');
        } catch (\Exception) {
            return '';
        }
    }

    // ── Delegated escaping helpers used directly in templates ──────────────

    public function escapeHtml(string $value): string
    {
        return $this->escaper->escapeHtml($value);
    }

    public function escapeHtmlAttr(string $value): string
    {
        return $this->escaper->escapeHtmlAttr($value);
    }

    public function escapeUrl(string $value): string
    {
        return $this->escaper->escapeUrl($value);
    }
}
