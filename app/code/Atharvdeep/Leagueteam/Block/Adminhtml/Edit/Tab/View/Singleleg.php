<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Atharvdeep\Leagueteam\Block\Adminhtml\Edit\Tab\View;
 
use Magento\Customer\Controller\RegistryConstants;
 
/**
 * Adminhtml customer recent orders grid block
 */
class Singleleg extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
 
    /**
     * @var \Atharvdeep\Leagueteam\Model\LeagueFactory
     */
    protected $leagueFactory;
 
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Atharvdeep\Leagueteam\Model\LeagueFactory $leagueFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
         $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->leagueFactory = $leagueFactory;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * Initialize the orders grid.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('league_view_legteam_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }
    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(10)->setCurPage(1);
    }
 
    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $collection = $this->leagueFactory->create()->getCollection()
                                            ->addFieldToSelect('*')
                                            ->addFieldToFilter('path', array('regexp' => $customerId.'[\\]'));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
 
    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'slno',
            ['header' => __('Sl No.'), 'index' => 'pk', 'type' => 'number', 'width' => '100px']
        );
        $this->addColumn(
            'member_id',
            [
               'header' => __('Member Id'),
               'index' => 'member_id',
            ]
        );
        $this->addColumn(
            'member_name',
            [
               'header' => __('Member Name'),
               'index' => 'member_name',
            ]
        );
        $this->addColumn(
            'sponsor_id',
            [
               'header' => __('Sponsor Id'),
               'index' => 'sponsor_id',
            ]
        );
        $this->addColumn(
            'sponsor_name',
            [
               'header' => __('Sponsor Name'),
               'index' => 'sponsor_name',
            ]
        );
        $this->addColumn(
            'created_at',
            [
               'header' => __('Joining Date'),
               'index' => 'created_at',
            ]
        );
        return parent::_prepareColumns();
    }
 
    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }
    /**
     * {@inheritdoc}
     */
    // public function getRowUrl($row)
    // {
    //     return $this->getUrl('catalog/product/edit', ['id' => $row->getProductId()]);
    // }
}
