<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model;

use BS23\ChatIntegration\Api\Data\ChatEntryInterface;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry as ChatEntryResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Chat Entry model.
 *
 * Thin data model – all business logic lives in the repository and ViewModel.
 */
class ChatEntry extends AbstractModel implements ChatEntryInterface
{
    /** @var string Event prefix for observers */
    protected $_eventPrefix = 'bs23_chat_entry';

    protected function _construct(): void
    {
        $this->_init(ChatEntryResource::class);
    }

    public function getEntryId(): ?int
    {
        $id = $this->getData(self::ENTRY_ID);
        return $id !== null ? (int) $id : null;
    }

    public function setEntryId(int $entryId): static
    {
        return $this->setData(self::ENTRY_ID, $entryId);
    }

    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    public function setName(string $name): static
    {
        return $this->setData(self::NAME, $name);
    }

    public function getUrl(): string
    {
        return (string) $this->getData(self::URL);
    }

    public function setUrl(string $url): static
    {
        return $this->setData(self::URL, $url);
    }

    public function getIcon(): ?string
    {
        $icon = $this->getData(self::ICON);
        return $icon !== null ? (string) $icon : null;
    }

    public function setIcon(?string $icon): static
    {
        return $this->setData(self::ICON, $icon);
    }

    public function getIconBgColor(): ?string
    {
        $color = $this->getData(self::ICON_BG_COLOR);
        return $color !== null && $color !== '' ? (string) $color : null;
    }

    public function setIconBgColor(?string $color): static
    {
        return $this->setData(self::ICON_BG_COLOR, $color);
    }

    public function getIconBgEnabled(): bool
    {
        return (bool) $this->getData(self::ICON_BG_ENABLED);
    }

    public function setIconBgEnabled(bool $enabled): static
    {
        return $this->setData(self::ICON_BG_ENABLED, $enabled ? 1 : 0);
    }

    public function getSortOrder(): int
    {
        return (int) $this->getData(self::SORT_ORDER);
    }

    public function setSortOrder(int $sortOrder): static
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function getIsEnabled(): bool
    {
        return (bool) $this->getData(self::IS_ENABLED);
    }

    public function setIsEnabled(bool $isEnabled): static
    {
        return $this->setData(self::IS_ENABLED, $isEnabled);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /** @return int[] */
    public function getStoreIds(): array
    {
        $ids = $this->getData(self::STORE_IDS);
        if (is_array($ids)) {
            return array_map('intval', $ids);
        }
        return [0];
    }

    /** @param int[] $storeIds */
    public function setStoreIds(array $storeIds): static
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }
}
