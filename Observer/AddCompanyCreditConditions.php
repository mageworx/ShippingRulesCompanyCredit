<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace MageWorx\ShippingRulesCompanyCredit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddCompanyCreditConditions implements ObserverInterface
{
    /**
     * @var \Magento\CompanyCredit\Api\CreditDataProviderInterface
     */
    private $creditDataProvider;

    /**
     * @param \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
     */
    public function __construct(
        \Magento\CompanyCredit\Api\CreditDataProviderInterface $creditDataProvider
    ) {
        $this->creditDataProvider = $creditDataProvider;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var DataObject $companyData */
        $companyData = $observer->getEvent()->getData('data_object');
        if ($companyData instanceof DataObject) {
            $companyId = (int)$companyData->getData('company_id');
            if ($companyId) {
                try {
                    $creditCompanyData = $this->creditDataProvider->get($companyId);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $noSuchEntityException) {
                    return;
                }

                $companyData->setData('company_credit_available_limit', $creditCompanyData->getAvailableLimit());
                $companyData->setData('company_credit_balance', $creditCompanyData->getBalance());
                $companyData->setData('company_credit_exceed_limit', $creditCompanyData->getExceedLimit());
            }
        }
    }
}
