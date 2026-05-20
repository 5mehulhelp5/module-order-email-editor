<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Api\Data;

/**
 * One audit-log row representing an order-email change.
 */
interface EmailChangeHistoryInterface
{
    public const HISTORY_ID               = 'history_id';
    public const ORDER_ID                 = 'order_id';
    public const INCREMENT_ID             = 'increment_id';
    public const OLD_EMAIL                = 'old_email';
    public const NEW_EMAIL                = 'new_email';
    public const CHANGED_BY_ADMIN_ID      = 'changed_by_admin_id';
    public const CHANGED_BY_ADMIN_NAME    = 'changed_by_admin_name';
    public const CUSTOMER_RECORD_UPDATED  = 'customer_record_updated';
    public const IP_ADDRESS               = 'ip_address';
    public const CHANGED_AT               = 'changed_at';

    /** @return int|null */
    public function getHistoryId(): ?int;

    /**
     * @param int $historyId
     * @return self
     */
    public function setHistoryId(int $historyId): self;

    /** @return int */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return self
     */
    public function setOrderId(int $orderId): self;

    /** @return string */
    public function getIncrementId(): string;

    /**
     * @param string $incrementId
     * @return self
     */
    public function setIncrementId(string $incrementId): self;

    /** @return string */
    public function getOldEmail(): string;

    /**
     * @param string $email
     * @return self
     */
    public function setOldEmail(string $email): self;

    /** @return string */
    public function getNewEmail(): string;

    /**
     * @param string $email
     * @return self
     */
    public function setNewEmail(string $email): self;

    /** @return int|null */
    public function getChangedByAdminId(): ?int;

    /**
     * @param int|null $adminId
     * @return self
     */
    public function setChangedByAdminId(?int $adminId): self;

    /** @return string|null */
    public function getChangedByAdminName(): ?string;

    /**
     * @param string|null $name
     * @return self
     */
    public function setChangedByAdminName(?string $name): self;

    /** @return bool */
    public function isCustomerRecordUpdated(): bool;

    /**
     * @param bool $flag
     * @return self
     */
    public function setCustomerRecordUpdated(bool $flag): self;

    /** @return string|null */
    public function getIpAddress(): ?string;

    /**
     * @param string|null $ip
     * @return self
     */
    public function setIpAddress(?string $ip): self;

    /** @return string|null ISO 8601 timestamp, or null when not yet persisted. */
    public function getChangedAt(): ?string;

    /**
     * @param string $changedAt
     * @return self
     */
    public function setChangedAt(string $changedAt): self;
}
