<?php

declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Console\Command;

use ETechFlow\OrderEmailEditor\Api\EmailChangeHistoryRepositoryInterface;
use ETechFlow\OrderEmailEditor\Model\Config;
use ETechFlow\OrderEmailEditor\Model\EmailChangeHistoryRepository;
use ETechFlow\OrderEmailEditor\Model\LicenseValidator;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State as AppState;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `bin/magento etechflow:oee:verify`
 *
 * Headless end-to-end check of ETechFlow_OrderEmailEditor. Confirms:
 *   - License + Config evaluate.
 *   - DI resolves the repository, controllers, service classes.
 *   - The DB table `etechflow_email_change_history` exists.
 *   - The repository interface preference is wired correctly.
 *
 * Idempotent. No DB writes — checks only.
 */
class VerifyCommand extends Command
{
    public function __construct(
        private readonly AppState $appState,
        private readonly ObjectManagerInterface $objectManager,
        private readonly LicenseValidator $licenseValidator,
        private readonly Config $config,
        private readonly ResourceConnection $resource
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('etechflow:oee:verify')
            ->setDescription('Headless end-to-end check of the ETechFlow Order Email Editor module.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->appState->setAreaCode(Area::AREA_ADMINHTML);
        }

        $output->writeln('<info>=== ETechFlow Order Email Editor verify ===</info>');
        $output->writeln('');

        $allPassed = true;

        try {
            $this->step($output, '1. LicenseValidator evaluates without throwing');
            $host = $this->licenseValidator->getCurrentHost();
            $valid = $this->licenseValidator->isValid();
            $dev = $this->licenseValidator->isDevHost();
            $this->pass($output, sprintf(
                'host=%s; dev_host=%s; valid=%s',
                $host !== '' ? $host : '(empty)',
                $dev ? 'yes' : 'no',
                $valid ? 'yes' : 'no'
            ));

            $this->step($output, '2. Config.isEnabled() returns a boolean');
            $enabled = $this->config->isEnabled();
            $this->pass($output, 'enabled=' . ($enabled ? 'yes' : 'no'));

            $this->step($output, '3. Repository interface preference resolves');
            $repo = $this->objectManager->get(EmailChangeHistoryRepositoryInterface::class);
            if (!$repo instanceof EmailChangeHistoryRepository) {
                throw new \RuntimeException(sprintf(
                    'Expected EmailChangeHistoryRepository, got %s',
                    get_class($repo)
                ));
            }
            $this->pass($output);

            $this->step($output, '4. Update controller resolves via DI');
            $controller = $this->objectManager->get(\ETechFlow\OrderEmailEditor\Controller\Adminhtml\Order\Update::class);
            if (!$controller) {
                throw new \RuntimeException('Update controller DI resolution failed');
            }
            $this->pass($output);

            $this->step($output, '5. UpdateOrderEmail service resolves via DI');
            $service = $this->objectManager->get(\ETechFlow\OrderEmailEditor\Model\Service\UpdateOrderEmail::class);
            if (!$service) {
                throw new \RuntimeException('UpdateOrderEmail service DI resolution failed');
            }
            $this->pass($output);

            $this->step($output, '6. DB table etechflow_email_change_history exists');
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('etechflow_email_change_history');
            if (!$connection->isTableExists($tableName)) {
                throw new \RuntimeException(sprintf('Table %s does not exist — run bin/magento setup:upgrade', $tableName));
            }
            $this->pass($output, $tableName);

            $this->step($output, '7. History table has the expected columns');
            $columns = $connection->describeTable($tableName);
            $expected = ['history_id', 'order_id', 'increment_id', 'old_email', 'new_email', 'changed_at'];
            $missing = array_diff($expected, array_keys($columns));
            if (!empty($missing)) {
                throw new \RuntimeException('Missing columns: ' . implode(', ', $missing));
            }
            $this->pass($output, count($columns) . ' columns present');

            $output->writeln('');
            $output->writeln('<info>✅ ALL CHECKS PASSED. v1.0.0 verified.</info>');
        } catch (\Throwable $e) {
            $allPassed = false;
            $output->writeln('');
            $output->writeln('<error>❌ FAIL: ' . $e->getMessage() . '</error>');
            $output->writeln('<error>at ' . $e->getFile() . ':' . $e->getLine() . '</error>');
        }

        return $allPassed ? Command::SUCCESS : Command::FAILURE;
    }

    private function step(OutputInterface $output, string $label): void
    {
        $output->write('  ' . $label . ' ... ');
    }

    private function pass(OutputInterface $output, string $detail = ''): void
    {
        $output->writeln('<info>OK</info>' . ($detail !== '' ? " ({$detail})" : ''));
    }
}
