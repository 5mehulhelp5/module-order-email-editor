<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model;

use ETechFlow\OrderEmailEditor\Api\Data\EmailChangeHistoryInterface;
use ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory as ResourceHistory;
use Magento\Framework\Model\AbstractModel;

class EmailChangeHistory extends AbstractModel implements EmailChangeHistoryInterface
{
    protected $_eventPrefix = 'etechflow_email_change_history';

    protected function _construct(): void
    {
        $this->_init(ResourceHistory::class);
    }

    public function getHistoryId(): ?int
    {
        $v = $this->getData(self::HISTORY_ID);
        return $v === null ? null : (int) $v;
    }

    public function setHistoryId(int $historyId): EmailChangeHistoryInterface
    {
        return $this->setData(self::HISTORY_ID, $historyId);
    }

    public function getOrderId(): int
    {
        return (int) $this->getData(self::ORDER_ID);
    }

    public function setOrderId(int $orderId): EmailChangeHistoryInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getIncrementId(): string
    {
        return (string) $this->getData(self::INCREMENT_ID);
    }

    public function setIncrementId(string $incrementId): EmailChangeHistoryInterface
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    public function getOldEmail(): string
    {
        return (string) $this->getData(self::OLD_EMAIL);
    }

    public function setOldEmail(string $email): EmailChangeHistoryInterface
    {
        return $this->setData(self::OLD_EMAIL, $email);
    }

    public function getNewEmail(): string
    {
        return (string) $this->getData(self::NEW_EMAIL);
    }

    public function setNewEmail(string $email): EmailChangeHistoryInterface
    {
        return $this->setData(self::NEW_EMAIL, $email);
    }

    public function getChangedByAdminId(): ?int
    {
        $v = $this->getData(self::CHANGED_BY_ADMIN_ID);
        return $v === null ? null : (int) $v;
    }

    public function setChangedByAdminId(?int $adminId): EmailChangeHistoryInterface
    {
        return $this->setData(self::CHANGED_BY_ADMIN_ID, $adminId);
    }

    public function getChangedByAdminName(): ?string
    {
        $v = $this->getData(self::CHANGED_BY_ADMIN_NAME);
        return $v === null ? null : (string) $v;
    }

    public function setChangedByAdminName(?string $name): EmailChangeHistoryInterface
    {
        return $this->setData(self::CHANGED_BY_ADMIN_NAME, $name);
    }

    public function isCustomerRecordUpdated(): bool
    {
        return (bool) $this->getData(self::CUSTOMER_RECORD_UPDATED);
    }

    public function setCustomerRecordUpdated(bool $flag): EmailChangeHistoryInterface
    {
        return $this->setData(self::CUSTOMER_RECORD_UPDATED, $flag ? 1 : 0);
    }

    public function getIpAddress(): ?string
    {
        $v = $this->getData(self::IP_ADDRESS);
        return $v === null ? null : (string) $v;
    }

    public function setIpAddress(?string $ip): EmailChangeHistoryInterface
    {
        return $this->setData(self::IP_ADDRESS, $ip);
    }

    public function getChangedAt(): ?string
    {
        $v = $this->getData(self::CHANGED_AT);
        return $v === null ? null : (string) $v;
    }

    public function setChangedAt(string $changedAt): EmailChangeHistoryInterface
    {
        return $this->setData(self::CHANGED_AT, $changedAt);
    }
}
