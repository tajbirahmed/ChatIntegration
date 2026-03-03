<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Block\Adminhtml\ChatEntry\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * "Back" button — navigates to the chat entry grid.
 */
class Back extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'      => __('Back'),
            'on_click'   => sprintf("location.href = '%s';", $this->getUrl('*/*/')),
            'class'      => 'back',
            'sort_order' => 10,
        ];
    }
}
