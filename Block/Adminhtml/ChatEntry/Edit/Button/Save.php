<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * "Save Chat Entry" button — submits the UI component form and redirects to the grid.
 */
class Save extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save Chat Entry'),
            'class'          => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'bs23_chatentry_form.bs23_chatentry_form',
                                'actionName' => 'save',
                                'params'     => [true],
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 90,
        ];
    }
}
