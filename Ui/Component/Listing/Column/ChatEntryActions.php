<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Adds Edit / Delete action links to each row in the Chat Entries grid.
 */
class ChatEntryActions extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $columnName = $this->getData('name');

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['entry_id'])) {
                continue;
            }

            $item[$columnName] = [
                'edit' => [
                    'href'  => $this->urlBuilder->getUrl(
                        'bs23_chatintegration/chatentry/edit',
                        ['entry_id' => $item['entry_id']]
                    ),
                    'label' => __('Edit'),
                ],
                'delete' => [
                    'href'    => $this->urlBuilder->getUrl(
                        'bs23_chatintegration/chatentry/delete',
                        ['entry_id' => $item['entry_id']]
                    ),
                    'label'   => __('Delete'),
                    'confirm' => [
                        'title'   => __('Delete "%1"', $item['name'] ?? ''),
                        'message' => __('Are you sure you want to delete this chat entry?'),
                    ],
                    'post' => true,
                ],
            ];
        }

        return $dataSource;
    }
}
