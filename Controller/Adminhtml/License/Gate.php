<?php

declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Controller\Adminhtml\License;

use ETechFlow\OrderEmailEditor\Model\LicenseValidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * License-required gate page. Shows plan cards + "Enter License Key".
 * Redirects to the Edit History grid when the license is already valid.
 */
class Gate extends Action
{
    public const ADMIN_RESOURCE = 'ETechFlow_OrderEmailEditor::config';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly LicenseValidator $licenseValidator
    ) {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        if ($this->licenseValidator->isValid()) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('order_email_editor/history/index');
        }

        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->prepend(__('Order Email Editor — License Required'));
        return $page;
    }
}
