<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Model;

use ETechFlow\OrderEmailEditor\Api\Data\EmailChangeHistoryInterface;
use ETechFlow\OrderEmailEditor\Api\EmailChangeHistoryRepositoryInterface;
use ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory as HistoryResource;
use ETechFlow\OrderEmailEditor\Model\ResourceModel\EmailChangeHistory\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class EmailChangeHistoryRepository implements EmailChangeHistoryRepositoryInterface
{
    public function __construct(
        private readonly HistoryResource $resource,
        private readonly EmailChangeHistoryFactory $historyFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly SearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    public function save(EmailChangeHistoryInterface $entity): EmailChangeHistoryInterface
    {
        try {
            $this->resource->save($entity);
        } catch (\Throwable $e) {
            throw new CouldNotSaveException(__('Unable to save email-change history entry: %1', $e->getMessage()), $e);
        }
        return $entity;
    }

    public function getById(int $historyId): EmailChangeHistoryInterface
    {
        $model = $this->historyFactory->create();
        $this->resource->load($model, $historyId);
        if (! $model->getId()) {
            throw new NoSuchEntityException(__('Email-change history entry %1 not found.', $historyId));
        }
        return $model;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $results = $this->searchResultsFactory->create();
        $results->setItems($collection->getItems());
        $results->setTotalCount($collection->getSize());
        $results->setSearchCriteria($searchCriteria);
        return $results;
    }
}
