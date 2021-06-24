<?php

namespace Redbox\GivexGiftCard\Api\Data;

interface RequestInterface
{
    public const METHOD = 'method';
    public const URI = 'uri';
    public const GET_PARAMS = 'get_params';
    public const PARAMS = 'params';
    public const HEADERS = 'headers';

    /**
     * Get request Method.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Get Request's URI.
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get parameters of "GET" type.
     *
     * @return array
     */
    public function getGetParams(): array;

    /**
     * Get Body parameters or raw request string.
     *
     * @return array|string
     */
    public function getParams();

    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void;

    /**
     * Get all headers of the Request as an array.
     *
     * @return array
     */
    public function getHeaders(): array;
}
