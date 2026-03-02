<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Ui\DataProvider\ChatEntry\Form;

use BS23\ChatIntegration\Model\ResourceModel\ChatEntry\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Data provider for the Chat Entry edit form UI component.
 *
 * Prepares icon file data for the Magento file-uploader widget so that
 * existing icons render correctly when reopening the form.
 */
class DataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly RequestInterface $request,
        private readonly StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        // Filter to the entry being edited if an ID is present in the request
        $entryId = (int) $this->request->getParam('entry_id');
        if ($entryId) {
            $this->collection->addFieldToFilter('entry_id', $entryId);
        }

        foreach ($this->collection->getItems() as $item) {
            $data = $item->getData();

            // Transform stored icon path → array expected by the file-uploader widget
            if (!empty($data['icon'])) {
                $data['icon'] = $this->buildIconWidgetData((string) $data['icon']);
            }

            $this->loadedData[$item->getId()] = $data;
        }

        return $this->loadedData;
    }

    /**
     * Build the array structure the Magento UI file-uploader widget expects.
     *
     * @return array<int, array{name: string, url: string, file: string}>
     */
    private function buildIconWidgetData(string $iconPath): array
    {
        try {
            $base = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return [[
                'name' => basename($iconPath),
                'url'  => $base . 'bs23/chatentry/' . ltrim($iconPath, '/'),
                'file' => $iconPath,
            ]];
        } catch (\Exception) {
            return [];
        }
    }
}
