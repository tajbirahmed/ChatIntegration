<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Ui\DataProvider\ChatEntry\Listing;

use BS23\ChatIntegration\Model\ResourceModel\ChatEntry\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Data provider for the Chat Entry listing UI component (admin grid).
 */
class DataProvider extends AbstractDataProvider
{
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items'        => array_values($this->getCollection()->toArray()['items'] ?? []),
        ];
    }
}
