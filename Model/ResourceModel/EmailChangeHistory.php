<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EmailChangeHistory extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('etechflow_email_change_history', 'history_id');
    }
}
