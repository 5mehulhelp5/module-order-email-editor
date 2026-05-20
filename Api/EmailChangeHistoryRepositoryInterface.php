<?php
declare(strict_types=1);

namespace ETechFlow\OrderEmailEditor\Api;

use ETechFlow\OrderEmailEditor\Api\Data\EmailChangeHistoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface EmailChangeHistoryRepositoryInterface
{
    /**
     * Persist an email-change history entry.
     *
     * @param EmailChangeHistoryInterface $entity
     * @return EmailChangeHistoryInterface
     * @throws CouldNotSaveException
     */
    public function save(EmailChangeHistoryInterface $entity): EmailChangeHistoryInterface;

    /**
     * Load a history entry by id.
     *
     * @param int $historyId
     * @return EmailChangeHistoryInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $historyId): EmailChangeHistoryInterface;

    /**
     * Search for history entries matching the given criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;
}
