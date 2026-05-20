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
 */
class Config
{
    private const XML_PATH_ENABLED = 'etechflow_orderemaileditor/general/enabled';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param LicenseValidator     $licenseValidator
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly LicenseValidator $licenseValidator
    ) {
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
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
}
