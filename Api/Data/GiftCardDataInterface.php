<?php

namespace Redbox\GivexGiftCard\Api\Data;

/**
 * @api
 */
interface GiftCardDataInterface
{
    public const CARD_NUMBER = 'c';
    public const SECURITY_CODE = 'p';
    public const AMOUNT = 'a';
    public const BASE_AMOUNT = 'ba';

    /**
     * @return string
     */
    public function getCardNumber();

    /**
     *
     * @param string $cardNumber
     * @return $this;
     */
    public function setCardNumber($cardNumber);

    /**
     * @return string
     */
    public function getSecurityCode();

    /**
     *
     * @param string $securityCode
     * @return $this
     */
    public function setSecurityCode($securityCode);
}
