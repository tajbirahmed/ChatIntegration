<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model;

use BS23\ChatIntegration\Api\ChatEntryRepositoryInterface;
use BS23\ChatIntegration\Api\Data\ChatEntryInterface;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry as ChatEntryResource;
use BS23\ChatIntegration\Model\ResourceModel\ChatEntry\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Chat Entry repository implementation.
 */
class ChatEntryRepository implements ChatEntryRepositoryInterface
{
    public function __construct(
        private readonly ChatEntryFactory $chatEntryFactory,
        private readonly ChatEntryResource $chatEntryResource,
        private readonly CollectionFactory $collectionFactory,
        private readonly SearchResultsInterfaceFactory $searchResultsFactory
    ) {}

    /**
     * @inheritDoc
     */
    public function save(ChatEntryInterface $entry): ChatEntryInterface
    {
        try {
            $this->chatEntryResource->save($entry);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save chat entry: %1', $e->getMessage()),
                $e
            );
        }

        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $id): ChatEntryInterface
    {
        /** @var ChatEntry $entry */
        $entry = $this->chatEntryFactory->create();
        $this->chatEntryResource->load($entry, $id);

        if (!$entry->getId()) {
            throw new NoSuchEntityException(
                __('Chat entry with ID "%1" does not exist.', $id)
            );
        }

        return $entry;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();

        // Apply filter groups
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter(
                    $filter->getField(),
                    [$condition => $filter->getValue()]
                );
            }
        }

        // Apply sort orders
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(ChatEntryInterface $entry): bool
    {
        try {
            $this->chatEntryResource->delete($entry);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete chat entry: %1', $e->getMessage()),
                $e
            );
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->getById($id));
    }
}
