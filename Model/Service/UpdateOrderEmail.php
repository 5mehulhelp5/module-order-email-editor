<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model\Service;

use ETechFlow\OrderEmailEditor\Api\Data\EmailChangeHistoryInterface;
use ETechFlow\OrderEmailEditor\Api\EmailChangeHistoryRepositoryInterface;
use ETechFlow\OrderEmailEditor\Model\EmailChangeHistoryFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Core service: updates the customer email across every Magento table that
 * stores a copy of it, and writes an audit-log entry.
 *
 * Tables touched:
 *   sales_order.customer_email                 (Order entity setter)
 *   sales_order_address.email                  (both billing + shipping)
 *   sales_order_grid.customer_email            (auto via Magento's reindex observer)
 *   sales_invoice_grid.customer_email          (auto via same observer)
 *   sales_creditmemo_grid.customer_email       (auto via same observer)
 *   sales_shipment_grid.customer_email         (auto via same observer)
 *   customer_entity.email                      (only if "also update customer record")
 *   quote.customer_email + quote_address.email (defensive — only if quote still exists)
 *   etechflow_email_change_history (new row)
 */
class UpdateOrderEmail
{
    public function __construct(
        private readonly OrderRepositoryInterface           $orderRepository,
        private readonly CustomerRepositoryInterface        $customerRepository,
        private readonly EmailChangeHistoryRepositoryInterface $historyRepository,
        private readonly EmailChangeHistoryFactory          $historyFactory,
        private readonly ResourceConnection                 $resource,
        private readonly AdminSession                       $adminSession,
        private readonly RequestInterface                   $request,
        private readonly LoggerInterface                    $logger
    ) {
    }

    /**
     * @throws LocalizedException
     */
    public function execute(int $orderId, string $newEmail, bool $alsoUpdateCustomer): EmailChangeHistoryInterface
    {
        $newEmail = trim($newEmail);
        if ($newEmail === '' || ! filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }

        $order = $this->orderRepository->get($orderId);
        $oldEmail = (string) $order->getCustomerEmail();
        if (strcasecmp($oldEmail, $newEmail) === 0) {
            throw new LocalizedException(__('The new email is identical to the current email.'));
        }

        $connection = $this->resource->getConnection();
        $connection->beginTransaction();

        try {
            // 1. Order header
            $order->setCustomerEmail($newEmail);

            // 2. Both order addresses (billing + shipping)
            $billing  = $order->getBillingAddress();
            if ($billing) {
                $billing->setEmail($newEmail);
            }
            $shipping = $order->getShippingAddress();
            if ($shipping) {
                $shipping->setEmail($newEmail);
            }

            // OrderRepository::save() runs the standard sales_order_save_after
            // observer that reindexes all four grid tables automatically.
            $this->orderRepository->save($order);

            // 3. Optionally update the linked customer record
            $customerUpdated = false;
            if ($alsoUpdateCustomer && $order->getCustomerId()) {
                $customer = $this->customerRepository->getById((int) $order->getCustomerId());
                $customer->setEmail($newEmail);
                $this->customerRepository->save($customer);
                $customerUpdated = true;
            }

            // 4. Defensive quote sync (if the original quote still exists)
            $this->syncQuoteEmail((int) $order->getQuoteId(), $newEmail);

            // 5. Audit entry
            $entry = $this->writeHistory($order, $oldEmail, $newEmail, $customerUpdated);

            $connection->commit();
            return $entry;
        } catch (LocalizedException $e) {
            $connection->rollBack();
            throw $e;
        } catch (\Throwable $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new LocalizedException(__('Failed to update the order email: %1', $e->getMessage()), $e);
        }
    }

    private function syncQuoteEmail(int $quoteId, string $newEmail): void
    {
        if ($quoteId <= 0) {
            return;
        }
        $connection = $this->resource->getConnection();
        try {
            $connection->update(
                $this->resource->getTableName('quote'),
                ['customer_email' => $newEmail],
                ['entity_id = ?'  => $quoteId]
            );
            $connection->update(
                $this->resource->getTableName('quote_address'),
                ['email'          => $newEmail],
                ['quote_id = ?'   => $quoteId]
            );
        } catch (\Throwable $e) {
            // Non-fatal — log and continue. Most stores delete quote rows on order placement.
            $this->logger->warning('Quote-email sync skipped: ' . $e->getMessage());
        }
    }

    private function writeHistory(
        \Magento\Sales\Api\Data\OrderInterface $order,
        string $oldEmail,
        string $newEmail,
        bool $customerUpdated
    ): EmailChangeHistoryInterface {
        $admin = $this->adminSession->getUser();

        /** @var EmailChangeHistoryInterface $entry */
        $entry = $this->historyFactory->create();
        $entry->setOrderId((int) $order->getEntityId())
              ->setIncrementId((string) $order->getIncrementId())
              ->setOldEmail($oldEmail)
              ->setNewEmail($newEmail)
              ->setChangedByAdminId($admin ? (int) $admin->getId() : null)
              ->setChangedByAdminName($admin ? (string) $admin->getUserName() : null)
              ->setCustomerRecordUpdated($customerUpdated)
              ->setIpAddress($this->detectIp());

        return $this->historyRepository->save($entry);
    }

    private function detectIp(): ?string
    {
        $candidates = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        foreach ($candidates as $key) {
            $val = $this->request->getServer($key);
            if ($val) {
                // X-Forwarded-For can be a comma-separated chain — keep the first.
                $val = trim(explode(',', (string) $val)[0]);
                if (filter_var($val, FILTER_VALIDATE_IP)) {
                    return $val;
                }
            }
        }
        return null;
    }
}
