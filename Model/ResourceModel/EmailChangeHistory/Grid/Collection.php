<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory\Grid;

use ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory\Collection as HistoryCollection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

/**
 * UI Component data-source for the history grid. Follows the same constructor
 * signature used by Magento_Cms\Model\ResourceModel\Block\Grid\Collection so
 * the framework's CollectionFactory can hand us the standard `mainTable`,
 * `eventPrefix`, `eventObject`, `resourceModel` arguments (which we wire up
 * in etc/di.xml).
 */
class Collection extends HistoryCollection implements SearchResultInterface
{
    /** @var AggregationInterface|null */
    protected $aggregations;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        string                 $mainTable,
        string                 $eventPrefix,
        string                 $eventObject,
        string                 $resourceModel,
        string                 $model = Document::class,
        $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    public function getSearchCriteria()
    {
        return null;
    }

    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    public function getTotalCount()
    {
        return $this->getSize();
    }

    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(?array $items = null)
    {
        return $this;
    }
}
