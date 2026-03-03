<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * "Save and Continue Edit" button — submits form and redirects back to the edit page.
 */
class SaveAndContinue extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'          => __('Save and Continue Edit'),
            'class'          => 'save',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'bs23_chatentry_form.bs23_chatentry_form',
                                'actionName' => 'save',
                                'params'     => [true, ['back' => '1']],
                            ],
                        ],
                    ],
                ],
            ],
            'sort_order' => 80,
        ];
    }
}
