<?php
namespace Atharvdeep\Leagueteam\Controller\Single;

class Legteam extends \Magento\Framework\App\Action\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
