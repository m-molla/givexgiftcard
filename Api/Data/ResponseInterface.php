<?php

namespace Redbox\GivexGiftCard\Api\Data;

interface ResponseInterface
{
    /**
    * Get response data by the provided key.
    *
    * @param  string $key
    * @return string
    */
    public function getResponseData(string $key): string;
}
