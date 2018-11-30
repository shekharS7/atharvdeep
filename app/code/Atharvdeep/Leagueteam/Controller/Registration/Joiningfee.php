<?php
namespace Atharvdeep\Leagueteam\Controller\Registration;

class Joiningfee extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Event\Manager $eventManager
    ) {

        $this->pageFactory = $pageFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->cart = $cart;
        $this->productFactory = $productFactory;
        $this->cartRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->logger = $logger;
        $this->request = $request;
        $this->objectManager = $objectManager;
        $this->eventManager = $eventManager;
        parent::__construct($context);
    }
    
    public function execute()
    {
       
        $customerId = $this->request->getParam('customer_id');
        $store=$this->storeManager->getStore();
        $cart_id = $this->cartManagement->createEmptyCart();
        $cart = $this->cartRepository->get($cart_id);
        $cart->setStore($store);
        try {
        // if you have already had the buyer id, you can load customer directly
            $customer= $this->customerRepository->getById($customerId);
            $cart->setCurrency();
            $cart->assignCustomer($customer); //Assign quote to customer
            $addresses = $customer->getAddresses();
            $streetLine2 = null;
            foreach ($addresses as $addresse) {
                $countryId = $addresse->getCountryId();
                $city = $addresse->getCity();
                $streetLine1 =  $addresse->getStreet()[0] ? $addresse->getStreet()[0] : null;
                if (count($addresse->getStreet()) > 1) {
                    $streetLine2 =  $addresse->getStreet()[1];
                }
                $province =  $addresse->getRegion();
                $postcode = $addresse->getPostcode();
                $telephone = $addresse->getTelephone();
                $fax = $addresse->getFax();
            }

            $orderData =[
            'shipping_address' =>[
                'firstname'    => $customer->getFirstname(),
                'lastname'     => $customer->getLastname(),
                'street' => array (
                            '0' => $streetLine1,
                            '1' => $streetLine2,
                        ),
                'city' => $city,
                'country_id' => $countryId,
                'region' => $province,
                'postcode' => $postcode,
                'telephone' => $telephone,
                'fax' => $fax,
                'save_in_address_book' => 1
            ],
             'items'=>
            ['product_id'=>'20','qty'=>1]
            ];
        
            $product = $this->productFactory->create()->load($orderData['items']['product_id']);
            $cart->addProduct(
                $product,
                intval($orderData['items']['qty'])
            );
        
        
            $cart->getBillingAddress()->addData($orderData['shipping_address']);
            $cart->getShippingAddress()->addData($orderData['shipping_address']);
            $shippingAddress = $cart->getShippingAddress();
 
            // $shippingAddress->setCollectShippingRates(true)
            //     ->collectShippingRates()
            //     ->setShippingMethod('freeshipping_freeshipping'); //shipping method
             
            $cart->setPaymentMethod('checkmo'); //payment method
            $cart->setInventoryProcessed(false);
            $cart->getPayment()->importData(['method' => 'checkmo']);
            $cart->collectTotals();
            $cart = $this->cartRepository->get($cart->getId());
            $order_id = $this->cartManagement->placeOrder($cart->getId());
            
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('instamojo/redirect');
            return $resultRedirect;
        } catch (\Exception $e) {
            //echo $e->getMessage();
            $this->logger->debug('Joiningfee Error:: '.$e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('checkout/cart');
            return $resultRedirect;
        }
    }
}
