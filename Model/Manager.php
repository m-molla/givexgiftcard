<?php

namespace Redbox\GivexGiftCard\Model;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Exception\NoSuchEntityException;
use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface;
use Redbox\GivexGiftCard\Api\GetBalanceInterface;
use Redbox\GivexGiftCard\Api\GiftCardAccountManagerInterface;

class Manager implements GiftCardAccountManagerInterface
{
    protected $getBalance;

    public function __construct(GetBalanceInterface $getBalance)
    {
        $this->getBalance = $getBalance;
    }

    public function requestByCode(GiftCardDataInterface $giftCardData)
    {
        $cardNumber = $giftCardData->getCardNumber();
        $securityCode = $giftCardData->getSecurityCode();

        try {
            $data = $this->getBalance->execute($cardNumber, $securityCode, 'en');
        } catch (Exception $ex) {
            throw new NoSuchEntityException();
        }

        $balance = (float) filter_var($data->getBalance(), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if ($balance <= 0) {
            throw new InvalidArgumentException('Gift Card Account is invalid', 0, $ex);
        }
        $giftCardData->setBalance($balance);
        $giftCardData->setAmount($balance);

        return $giftCardData;
    }
}
