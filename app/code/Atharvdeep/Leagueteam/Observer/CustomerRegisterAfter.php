<?php

namespace Atharvdeep\Leagueteam\Observer;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegisterAfter implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;
    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
    */
    protected $objectManager;
    protected $request;
    /**
     * [__construct description]
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository [description]
     * @param \Magento\Framework\App\RequestInterface           $request            [description]
     * @param ObjectManagerInterface                            $objectManager      [description]
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\RequestInterface $request,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $memberId = $customer->getCustomAttribute('member_id')->getValue();
        $memberName = $customer->getFirstname()." ".$customer->getLastname();
     
        $sponsorId = $this->request->getPost('sponsor_id') ? $this->request->getPost('sponsor_id') : 0 ;
        $sponsorName = null;
        if ($sponsorId!=0) {
            $customerObj = $this->objectManager->
                                        get('Magento\Customer\Model\Customer')
                                        ->getCollection()
                                        ->addFieldToSelect('*')
                                 ->addFieldToFilter('member_id', $sponsorId);
            $sponsorName = $customerObj->getFirstItem()->getFirstname()." ".$customerObj->getFirstItem()->getLastname();
        }
        if ($memberId) {
                $league = $this->objectManager->get('Atharvdeep\Leagueteam\Model\League');
                $model = $league->getCollection()
                                 ->addFieldToSelect('member_id')
                                 ->addFieldToSelect('path')
                                 ->addFieldToSelect('sponsor_id')
                                 ->addFieldToFilter('member_id', $sponsorId);
            $memberPath = $model->getFirstItem()->getPath();
            try {
                if (empty($memberPath)) {
                    $path = $memberId;
                } else {
                    $path = $memberPath."\\".$memberId;
                }
                $data['member_id'] = $memberId;
                $data['member_name'] = $memberName;
                $data['sponsor_id'] = $sponsorId;
                $data['sponsor_name'] = $sponsorName;
                $data['level1'] = $memberId;
                $data['path'] = $path;
                $data['child_count'] = 0;
                $league->setData($data);
                $league->save();
                $parent = $sponsorId;
                if (!empty($parent)) {
                    $this->levelDistribution($memberId, $parent);
                }
            } catch (\Execption $e) {
                $logger->critical($e->getMessage());
            }
            $this->eventManager->dispatch(
                'after_registration_bv_distribution',
                [
                    'member_id' => $memberId,
                    'customer' => $customer
                ]
            );
        }
    }

    /**
    * @param int $memberId
    * @param int $parent
    *
    */

    public function levelDistribution($memberId, $parent)
    {
        $league =$this->objectManager->get('Atharvdeep\Leagueteam\Model\League');
        $count = 2;
        while ($count <= 10) {
            $model = $league->getCollection()
                                    ->addFieldToSelect('*')
                                    ->addFieldToFilter('member_id', $parent);
            //Exit form loop if no Parent
            $parent = $model->getFirstItem()->getSponsorId();
            if ($parent == null) {
                break;
            }
            // get level Members and primary key
            $level='level'.$count;
            $levelMembers = $model->getFirstItem()->getData($level);
            $primaryKey = $model->getFirstItem()->getPk();
            $allLevelMember = $levelMembers ? $levelMembers.','.$memberId : $memberId;
            // set level Members
            $league->load($primaryKey);
            $league->setData($level, $allLevelMember);
            if ($count==2) {
                $childCount = $model->getFirstItem()->getChildCount()+1;
                $league->setData('child_count', $childCount);
            }
            $league->setPk($primaryKey);
            $league->save();
            $count++;
        }
    }
}
