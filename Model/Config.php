<?php

declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Admin-config wrapper for ETechFlow_OrderEmailEditor.
 *
 * `isEnabled()` consults the licence validator first — an unlicensed
 * install silently hides the Edit Email button + history menu and
 * rejects the update endpoint.
 *
 * Tiered plans: `isCustomerSyncAllowed()` is gated by the subscription's
 * `customer_sync` feature flag (Professional / Enterprise). On Starter the
 * "also update the linked customer account" option is not honoured. For
 * dev / HMAC / bundle activations the flag defaults to ON (full features).
 */
class Config
{
    private const XML_PATH_ENABLED = 'etechflow_orderemaileditor/general/enabled';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LicenseValidator $licenseValidator
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        if (!$this->licenseValidator->isValid()) {
            return false;
        }
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Whether this plan may also push the email change to the linked
     * customer account. Gated by the plan's `customer_sync` flag; defaults
     * to true for non-portal (dev / HMAC / bundle) activations.
     */
    public function isCustomerSyncAllowed(): bool
    {
        return $this->licenseValidator->isFeatureEnabled('customer_sync', true);
    }
}
