<?php

namespace Redbox\GivexGiftCard\Model\Api;

use Magento\Framework\DataObject;
use Redbox\GivexGiftCard\Api\Data\RequestInterface;

class Request extends DataObject implements RequestInterface
{
    /**
     * Request constructor.
     *
     * @param string       $method
     * @param string       $uri
     * @param array        $getParams
     * @param array|string $params
     * @param array        $headers
     */
    public function __construct(
        string $method,
        string $uri = '',
        array $getParams = [],
        $params = [],
        array $headers = []
    ) {
        $this->setData([
            self::METHOD     => $method,
            self::URI        => $uri,
            self::GET_PARAMS => $getParams,
            self::PARAMS     => $params,
            self::HEADERS    => $headers,
        ]);
    }

    /**
     * Get request Method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->getData(self::METHOD);
    }

    /**
     * Get Request's URI.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->getData(self::URI);
    }

    /**
     * Get parameters of "GET" type.
     *
     * @return array
     */
    public function getGetParams(): array
    {
        return $this->getData(self::GET_PARAMS);
    }

    /**
     * Get Body Parameters.
     *
     * @return array|string
     */
    public function getParams()
    {
        return $this->getData(self::PARAMS);
    }

    /**
     * @param array $params
     */
    public function setParams(array $params): void
    {
        $this->setData(self::PARAMS, $params);
    }

    /**
     * Get all headers of the Request as an array.
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->getData(self::HEADERS);
    }
}
