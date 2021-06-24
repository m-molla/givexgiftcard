<?php

namespace Redbox\GivexGiftCard\Model\ResourceModel\Api;

use Redbox\GivexGiftCard\Api\GetBalanceInterface;
use Redbox\GivexGiftCard\Api\GivexConnectorInterface;
use Redbox\GivexGiftCard\Api\GivexRequestBuilderInterface;
use Redbox\GivexGiftCard\Api\Data\ResponseInterface;
use Magento\Framework\Validation\ValidationException;

class GetBalance implements GetBalanceInterface
{
    /**
     * GiveX Connector.
     *
     * @var GivexConnectorInterface
     */
    private $connector;

    /**
     * GiveX Request Builder.
     *
     * @var GivexRequestBuilderInterface
     */
    private $requestBuilder;

    /**
     * FetchBalance constructor.
     *
     * @param GivexConnectorInterface      $connector
     * @param GivexRequestBuilderInterface $requestBuilder
     */
    public function __construct(
        GivexConnectorInterface      $connector,
        GivexRequestBuilderInterface $requestBuilder
    ) {
        $this->connector      = $connector;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * Get balance of a GiveX gift card.
     *
     * @param  string $cardNumber
     * @param  string|null $securityCode
     * @param  string $languageCode
     * @return ResponseInterface
     * @throws ValidationException
     * @throws \RuntimeException
     * @throws \Zend_Http_Client_Exception
     */
    public function execute(
        string $cardNumber,
        ?string $securityCode,
        string $languageCode
    ): ResponseInterface {
        $request = $this->requestBuilder->buildRequest(
            self::METHOD,
            [
                $languageCode,
                'null',
                $this->requestBuilder->getUserId(),
                $this->requestBuilder->getPassword(),
                $cardNumber,
                $securityCode,
            ]
        );
        $response = $this->connector->sendGivexRequest(
            $request,
            self::RESPONSE_FIELD_MAPPING
        );
        $balanceText = $response->getResponseData(self::RESPONSE_BALANCE);
        $error = '';

        switch ($balanceText) {
            case self::WRONG_PIN_MESSAGE:
                $error = $balanceText;
                break;

            case self::WRONG_GC_MESSAGE_GIVEX:
                $error = self::WRONG_GC_MESSAGE;
                break;

            default:
                if (strpos($balanceText, self::EXPIRED_GC_MESSAGE_TEMPLATE_GIVEX) !== false) {
                    $error = self::EXPIRED_GC_MESSAGE;
                }
                break;
        }

        if (!empty($error)) {
            throw new ValidationException(__($error));
        }

        return $response;
    }
}
