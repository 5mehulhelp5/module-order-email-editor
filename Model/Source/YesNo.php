<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class YesNo implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('No')],
            ['value' => 1, 'label' => __('Yes')],
        ];
    }
}
