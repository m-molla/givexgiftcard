<?php

namespace Redbox\GivexGiftCard\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Redbox\GivexGiftCard\Api\Data\GiftCardAccountInterface;
use Redbox\GivexGiftCard\Api\GetBalanceInterface;
use Redbox\GivexGiftCard\Api\GiftCardAccountManagementInterface;
use Redbox\GivexGiftCard\Api\GiftCardAccountManagerInterface;
use Redbox\GivexGiftCard\Model\GiftCardDataFactory;

class GiftCardAccountManagement implements GiftCardAccountManagementInterface
{
    protected $getBalance;
    protected $quoteRepository;
    protected $manager;
    protected $helper;
    protected $giftCardDataFactory;

    public function __construct(
        GetBalanceInterface $getBalance,
        CartRepositoryInterface $quoteRepository,
        GiftCardAccountManagerInterface $manager,
        \Redbox\GivexGiftCard\Helper\Data $helper,
        GiftCardDataFactory $giftCardDataFactory
    ) {
        $this->getBalance = $getBalance;
        $this->quoteRepository = $quoteRepository;
        $this->manager = $manager;
        $this->helper = $helper;
        $this->giftCardDataFactory = $giftCardDataFactory;
    }
    public function checkGiftCard($cartId, $cardNumber)
    {
        $balance =  $this->getBalance->execute($cardNumber, null, 'en');
        return $balance->getBalance();
    }

    public function deleteByQuoteId($cartId, $giftCardAccountData): bool
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new CouldNotDeleteException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->giftCardDataFactory->create()->setCardNumber($giftCardAccountData);
            $giftCard->removeFromCart(true, $quote);
        } catch (\Throwable $e) {
            throw new CouldNotDeleteException(__("The gift card couldn't be deleted from the quote."));
        }
        return true;
    }

    public function getListByQuoteId($cartId)
    {
    }

    public function saveByQuoteId($cartId, GiftCardAccountInterface $giftCardAccountData): bool
    {
        if (!$giftCardAccountData->getGiftCards()) {
            throw new CouldNotSaveException(__('Requiring a composite gift card account.'));
        }
        /** @var  Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new CouldNotSaveException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        $giftCards = $giftCardAccountData->getGiftCards();
        $giftCards = array_shift($giftCards);


        try {
            $giftCard = $this->manager->requestByCode($giftCards);
            $giftCard->addToCart(true, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __("The gift card code couldn't be added. Verify your information and try again."),
                $e
            );
        }

        return true;
    }
}
