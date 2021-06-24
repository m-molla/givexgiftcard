<?php

namespace Redbox\GivexGiftCard\Api;

use Redbox\GivexGiftCard\Api\Data\RequestInterface;
use Redbox\GivexGiftCard\Api\Data\ResponseInterface;

interface GivexConnectorInterface
{
    /**
     * Send givex request.
     *
     * @param  RequestInterface $request
     * @param  array $keyMapping
     * @param bool $redeemRequest
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws \Zend_Http_Client_Exception
     * @throws \UnexpectedValueException
     * @throws \Exception
     */
    public function sendGivexRequest(
        RequestInterface $request,
        array $keyMapping,
        $redeemRequest = false
    ): ResponseInterface;
}
