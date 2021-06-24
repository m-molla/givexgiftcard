<?php

namespace Redbox\GivexGiftCard\Model\Api;

use Magento\Framework\DataObject;
use Redbox\GivexGiftCard\Api\Data\ResponseInterface;

class Response extends DataObject implements ResponseInterface
{
    /**
     * Get response data by the provided key.
     *
     * @param  string $key
     * @return string
     */
    public function getResponseData(string $key): string
    {
        return (string) parent::getData($key);
    }
}
