<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Controller\Adminhtml\History;

use ETechFlow\OrderEmailEditor\Model\Config;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'ETechFlow_OrderEmailEditor::view_history';

    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly Config $config
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        if (!$this->config->isEnabled()) {
            $this->messageManager->addNoticeMessage(
                __('ETechFlow Order Email Editor is disabled or unlicensed on this domain.')
            );
            return $this->resultRedirectFactory->create()->setPath('sales/order/index');
        }

        $page = $this->pageFactory->create();
        $page->setActiveMenu('ETechFlow_OrderEmailEditor::history');
        $page->getConfig()->getTitle()->prepend(__('Order Email Change History'));
        return $page;
    }
}
