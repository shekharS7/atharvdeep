<?php
namespace Atharvdeep\Leagueteam\Block\Single;

class Legteam extends \Magento\Framework\View\Element\Template
{
    protected $leagueFactory;
    protected $registry;
    protected $objectManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->leagueFactory = $leagueFactory;
        $this->objectManager = $objectManager;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
        //get collection of data
        $this->pageConfig->getTitle()->set(__('Single League Team'));
    }
    public function getCollection()
    {
        $memberId = $this->getMemberId();
        $collection = $this->leagueFactory->create()->getCollection()
                                            ->addFieldToSelect('*')
                                            ->addFieldToFilter('path', array('regexp' => $memberId.'[\\]'));
         return $collection;
    }

   
    public function getMemberId()
    {
        $customer = $this->customerSession->getCustomer();
        return $customer->getMemberId();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'singleleg.team.pager'
            )->setCollection(
                $this->getCollection() // assign collection to pager
            );
            $this->setChild('pager', $pager);// set pager block in layout
        }
        return $this;
    }
  
    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
