<?php

namespace Redbox\GivexGiftCard\Model\Api;

use Redbox\GivexGiftCard\Api\GivexResponseParserInterface;
use UnexpectedValueException;
use RuntimeException;
use Redbox\GivexGiftCard\Api\Data\ResponseInterface;
use Redbox\GivexGiftCard\Api\Data\ResponseInterfaceFactory;

class GivexResponseParser implements GivexResponseParserInterface
{
    /**
     * Response Factory.
     *
     * @var ResponseInterfaceFactory
     */
    private $responseFactory;

    /**
     * GivexResponseParser constructor.
     *
     * @param ResponseInterfaceFactory $responseFactory
     */
    public function __construct(
        ResponseInterfaceFactory $responseFactory
    ) {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Generate response by the provided key mapping.
     *
     * @param  array $keyMapping - mapping in int => string format
     * @param  array $data       - raw response data
     * @return ResponseInterface
     * @throws UnexpectedValueException
     * @throws RuntimeException
     */
    public function parseResponse(array $keyMapping, array $data): ResponseInterface
    {
        if (isset($data[self::ERROR_KEY])) {
            throw new UnexpectedValueException(sprintf(
                'Error response: %s - %s',
                $data[self::ERROR_KEY][self::ERROR_CODE_KEY] ?? '',
                $data[self::ERROR_KEY][self::ERROR_MESSAGE_KEY] ?? ''
            ));
        }

        if (!isset($data[self::RESULT_KEY])) {
            throw new RuntimeException("There's no 'result' in the response");
        }

        $responseData = [];

        foreach ($keyMapping as $id => $key) {
            $responseData[$key] = $data[self::RESULT_KEY][$id] ?? null;
        }

        return $this->responseFactory->create([
            'data' => $responseData,
        ]);
    }
}
