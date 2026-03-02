<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Api;

use BS23\ChatIntegration\Api\Data\ChatEntryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Chat Entry CRUD repository interface.
 */
interface ChatEntryRepositoryInterface
{
    /**
     * Save a chat entry.
     *
     * @throws CouldNotSaveException
     */
    public function save(ChatEntryInterface $entry): ChatEntryInterface;

    /**
     * Load a chat entry by its ID.
     *
     * @throws NoSuchEntityException
     */
    public function getById(int $id): ChatEntryInterface;

    /**
     * Retrieve list of chat entries matching the given criteria.
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Delete a chat entry.
     *
     * @throws CouldNotDeleteException
     */
    public function delete(ChatEntryInterface $entry): bool;

    /**
     * Delete a chat entry by its ID.
     *
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $id): bool;
}
