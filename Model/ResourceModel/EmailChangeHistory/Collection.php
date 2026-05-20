<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory;

use ETechFlow\OrderEmailEditor\Model\EmailChangeHistory as HistoryModel;
use ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory as HistoryResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'history_id';

    protected function _construct(): void
    {
        $this->_init(HistoryModel::class, HistoryResource::class);
    }
}
