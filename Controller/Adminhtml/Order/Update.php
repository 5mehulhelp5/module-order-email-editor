<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Controller\Adminhtml\Order;

use ETechFlow\OrderEmailEditor\Model\Config;
use ETechFlow\OrderEmailEditor\Model\Performance\Profiler;
use ETechFlow\OrderEmailEditor\Model\Service\UpdateOrderEmail;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Update extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'ETechFlow_OrderEmailEditor::edit_email';

    public function __construct(
        Context $context,
        private readonly JsonFactory       $jsonFactory,
        private readonly UpdateOrderEmail  $updateService,
        private readonly Config            $config,
        private readonly LoggerInterface   $logger
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        // Licence + master kill-switch — unlicensed installs silently
        // reject updates so a missing/expired key never lets an admin
        // edit and then realise the change wasn't actually persisted.
        if (!$this->config->isEnabled()) {
            return $result->setHttpResponseCode(403)
                ->setData(['success' => false, 'message' => __('ETechFlow Order Email Editor is disabled or unlicensed on this domain.')->render()]);
        }

        // Form-key validation (Magento adds this automatically for admin POSTs;
        // we double-check here so direct/CSRF requests get rejected).
        if (! $this->_formKeyValidator->validate($this->getRequest())) {
            return $result->setHttpResponseCode(400)
                ->setData(['success' => false, 'message' => __('Invalid security token. Please refresh the page and try again.')->render()]);
        }

        $orderId            = (int) $this->getRequest()->getParam('order_id');
        $newEmail           = (string) $this->getRequest()->getParam('new_email', '');
        $alsoUpdateCustomer = (bool)   $this->getRequest()->getParam('update_customer', false);

        if ($orderId <= 0) {
            return $result->setHttpResponseCode(400)
                ->setData(['success' => false, 'message' => __('Missing order_id.')->render()]);
        }

        $span = Profiler::start('ETechFlow_OEE_UpdateOrderEmail');
        try {
            $entry = $this->updateService->execute($orderId, $newEmail, $alsoUpdateCustomer);
            return $result->setData([
                'success'                 => true,
                'message'                 => __('Order email updated successfully.')->render(),
                'history_id'              => $entry->getHistoryId(),
                'new_email'               => $entry->getNewEmail(),
                'customer_record_updated' => $entry->isCustomerRecordUpdated(),
            ]);
        } catch (LocalizedException $e) {
            return $result->setHttpResponseCode(422)
                ->setData(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            return $result->setHttpResponseCode(500)
                ->setData(['success' => false, 'message' => __('Unexpected error. Check the system log.')->render()]);
        } finally {
            Profiler::stop($span);
        }
    }
}
