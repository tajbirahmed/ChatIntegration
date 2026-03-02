<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for chat layout style options.
 */
class LayoutStyle implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'vertical',   'label' => __('Vertical Stack')],
            ['value' => 'horizontal', 'label' => __('Horizontal Row')],
            ['value' => 'circular',   'label' => __('Circular / Ring Layout')],
        ];
    }
}
