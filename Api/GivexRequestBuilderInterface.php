<?php

namespace Redbox\GivexGiftCard\Api;

use Redbox\GivexGiftCard\Api\Data\RequestInterface;
use UnexpectedValueException;

interface GivexRequestBuilderInterface
{
    public const XML_PATH_USERID = 'givex/api/userid';
    public const XML_PATH_PASSWORD = 'givex/api/password';

    /**
     * Build GiveX request.
     *
     * @param  string $method
     * @param  array  $params
     * @return RequestInterface
     */
    public function buildRequest(string $method, array $params): RequestInterface;

    /**
     * Get User ID.
     *
     * @return string
     * @throws UnexpectedValueException
     */
    public function getUserId(): string;

    /**
     * Get Password.
     *
     * @return string
     * @throws UnexpectedValueException
     */
    public function getPassword(): string;
}
