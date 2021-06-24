<?php

namespace Redbox\GivexGiftCard\Controller\Balance;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface;
use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterfaceFactory;
use Redbox\GivexGiftCard\Api\GiftCardAccountManagerInterface;

class Check implements HttpPostActionInterface
{
    private $request;
    private $resultFactory;
    private $getBalance;
    private $checkoutSession;
    private $messageManager;
    private $localeCurrency;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        \Redbox\GivexGiftCard\Api\GetBalanceInterface $getBalance,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
             \Magento\Framework\Locale\CurrencyInterface $localeCurrency
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->getBalance = $getBalance;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->localeCurrency = $localeCurrency;
    }

    public function execute()
    {
        $cardNumber = $this->request->getPostValue('gift_card_number');
        $securityCode = $this->request->getPostValue('gift_card_security_code');

        try {
            $data = $this->getBalance->execute($cardNumber, $securityCode, 'en');
            $balance = (float) filter_var($data->getBalance(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $currency = $data->getCurrency();
            if($currency){
                $symbol = $this->localeCurrency->getCurrency($currency)->getSymbol();
            }else{
               $symbol = $this->localeCurrency->getDefaultCurrency() ;
            }
            
            $this->messageManager->addSuccessMessage(__('Your balance is %1 %2', $balance, $symbol));
        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
        }

        /** @var Redirect $redirect */
        $redirect =  $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $redirect->setRefererUrl();
    }
}
