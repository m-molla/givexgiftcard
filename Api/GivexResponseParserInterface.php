<?php

namespace Redbox\GivexGiftCard\Api;

use UnexpectedValueException;
use RuntimeException;
use Redbox\GivexGiftCard\Api\Data\ResponseInterface;

interface GivexResponseParserInterface
{
    public const RESULT_KEY = 'result';
    public const ERROR_KEY = 'error';
    public const ERROR_MESSAGE_KEY = 'message';
    public const ERROR_CODE_KEY = 'code';

    /**
     * Generate response by the provided key mapping.
     *
     * @param  array $keyMapping - mapping in int => string format
     * @param  array $data       - raw response data
     * @return ResponseInterface
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    public function parseResponse(array $keyMapping, array $data): ResponseInterface;
}
