<?php

namespace Redbox\GivexGiftCard\Api;

use Redbox\GivexGiftCard\Api\Data\ResponseInterface;

interface GetBalanceInterface
{
    public const METHOD = 'dc_994';
    public const RESPONSE_BALANCE = 'balance';
    public const RESPONSE_CURRENCY = 'currency';
    public const WRONG_PIN_MESSAGE = 'Invalid security code';
    public const WRONG_GC_MESSAGE_GIVEX = 'Cert not exist';
    public const EXPIRED_GC_MESSAGE_TEMPLATE_GIVEX = 'Cert expired';
    public const EXPIRED_GC_MESSAGE = 'Gift Card is expired';
    public const WRONG_GC_MESSAGE = 'Gift Card is not found. Please check the number and try again';
    public const RESPONSE_FIELD_MAPPING = [
        2 => self::RESPONSE_BALANCE,
        5 => self::RESPONSE_CURRENCY,
    ];

    /**
     * Get balance of a GiveX gift card.
     *
     * @param  string $cardNumber
     * @param  string|null $securityCode
     * @param  string $languageCode
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws \Zend_Http_Client_Exception
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(
        string $cardNumber,
        ?string $securityCode,
        string $languageCode
    ): ResponseInterface;
}
