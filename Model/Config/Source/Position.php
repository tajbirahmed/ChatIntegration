<?php

declare(strict_types=1);

namespace BS23\ChatIntegration\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for bubble position options.
 */
class Position implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'lower_right', 'label' => __('Lower Right (default)')],
            ['value' => 'lower_left',  'label' => __('Lower Left')],
            ['value' => 'upper_right', 'label' => __('Upper Right')],
        ];
    }
}
