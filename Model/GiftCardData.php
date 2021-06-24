<?php

namespace Redbox\GivexGiftCard\Model;

use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface;
use Redbox\GivexGiftCard\Helper\Data;

class GiftCardData extends DataObject implements GiftCardDataInterface
{
    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Checkout Session
     *
     * @var Session
     */
    protected $checkoutSession;

    protected $quoteRepository;


    protected $helper;


    public function __construct(
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        CartRepositoryInterface $quoteRepository,
        Data $helper,
        array $data = array()
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $storeManager;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;

        parent::__construct($data);
    }
    public function getCardNumber()
    {
        return $this->getData(self::CARD_NUMBER);
    }

    public function getSecurityCode()
    {
        return $this->getData(self::SECURITY_CODE);
    }

    public function setCardNumber($cardNumber)
    {
        $this->setData(self::CARD_NUMBER, $cardNumber);
        return $this;
    }

    public function setSecurityCode($securityCode)
    {
        $this->setData(self::SECURITY_CODE, $securityCode);
        return $this;
    }

    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    public function setAmount($amount)
    {
        $this->setData(self::AMOUNT, $amount);
        return $this;
    }

    public function getBaseAamount()
    {
        $this->getData(self::BASE_AMOUNT);
    }

    public function setBaseAmount($baseAmount)
    {
        $this->setData(self::BASE_AMOUNT, $baseAmount);
        return $this;
    }


    /**
     * Add gift card to quote gift card storage
     *
     * @param bool $saveQuote
     * @param Quote|null $quote
     * @return $this
     * @throws LocalizedException
     */
    public function addToCart($saveQuote = true, $quote = null)
    {
        if ($quote === null) {
            $quote = $this->checkoutSession->getQuote();
        }

        $cards = $this->helper->getCards($quote);
        if (!$cards) {
            $cards = [];
        } else {
            foreach ($cards as $one) {
                if ($one[self::CARD_NUMBER] == $this->getCardNumber()) {
                    throw new LocalizedException(
                        __('This gift card account is already in the quote.')
                    );
                }
            }
        }
        $cards[] = [
                self::CARD_NUMBER => $this->getCardNumber(),
                self::SECURITY_CODE => $this->getSecurityCode(),
                self::AMOUNT => $this->getAmount(),
                self::BASE_AMOUNT => $this->getBaseAamount(),
            ];
        $this->helper->setCards($quote, $cards);

        if ($saveQuote) {
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }


        return $this;
    }

    /**
     * Remove gift card from quote gift card storage
     *
     * @param bool $saveQuote
     * @param \Magento\Quote\Model\Quote|null $quote
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeFromCart($saveQuote = true, $quote = null)
    {
        if (!$this->getCardNumber()) {
            throw new LocalizedException(__('Please correct the gift card account code: "%1".', $this->getCardNumber()));
        }
        if ($quote === null) {
            $quote = $this->checkoutSession->getQuote();
        }

        $cards = $this->helper->getCards($quote);
        if ($cards) {
            foreach ($cards as $k => $one) {
                if ($one[GiftCardDataInterface::CARD_NUMBER] == $this->getCardNumber()) {
                    unset($cards[$k]);
                    $this->helper->setCards($quote, $cards);

                    if ($saveQuote) {
                        $quote->collectTotals();
                        $this->quoteRepository->save($quote);
                    }
                    return $this;
                }
            }
        }

        throw new LocalizedException(__('This gift card account wasn\'t found in the quote.'));
    }
}
