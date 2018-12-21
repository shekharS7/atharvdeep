<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Atharvdeep\Leagueteam\Model\CronJob;

use Magento\Store\Model\StoresConfig;
use Magento\Sales\Model\Order;

class UpdateDays
{
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    protected $scopeConfig;
    protected $orderManagement;
    protected $objectManager;
    /**
     * @param StoresConfig $storesConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->orderCollectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->objectManager = $objectManager;
        $this->leagueFactory = $leagueFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $league =$this->leagueFactory->create();
        $resource = $this->objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('atharvdeep_leagueteam');
        //Select Data from table
        $sql = "Select * FROM " . $tableName." where (LENGTH(level2) - LENGTH(REPLACE(level2, ',', ''))+1) = 5  and floor(TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`))/86400) <= 15";
        $result = $connection->fetchAll($sql);
        foreach ($result as $value) {
            $league->load($value['pk']);
            $league->setData('manager', 'Silver');
            $league->setPk($value['pk']);
            $league->save();
        }
        $this->logger->debug(__METHOD__);
    }
}
