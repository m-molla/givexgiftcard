<?php

namespace Redbox\GivexGiftCard\Api;

use Redbox\GivexGiftCard\Api\Data\RequestInterface;

interface ConnectorInterface
{
    /**
     * Header content type key to be used in a request.
     *
     * @var string
     */
    public const CONTENT_TYPE_VALUE = 'application/json';

    /**
     * Header content type to be used in a request.
     *
     * @var string
     */
    public const CONTENT_TYPE_KEY = 'Content-Type';

    /**
     * Base URL that is to be overridden.
     *
     * @var string
     */
    public const BASE_URL = '';

    /**
     * Whether or not we need to check the code of the response.
     *
     * @var bool
     */
    public const CORRECT_RESPONSE_CODE_REQUIRED = true;

    /**
     * Whether or not we need to parse the response.
     *
     * @var bool
     */
    public const RESPONSE_PARSING_REQUIRED = true;

    /**
     * Correct response code for response verification.
     *
     * @var int
     */
    public const CORRECT_RESPONSE_CODE = 200;

    /**
     * Send Request.
     *
     * @param  RequestInterface $request
     * @return array
     * @throws \RuntimeException
     * @throws \Zend_Http_Client_Exception
     */
    public function sendRequest(
        RequestInterface $request
    ): array;
}
