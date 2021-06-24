<?php

namespace Redbox\GivexGiftCard\Model\Api;

use Redbox\GivexGiftCard\Api\Data\RequestInterface;
use Redbox\GivexGiftCard\Helper\ConnectorHelper;
use Redbox\GivexGiftCard\Model\Api\Connector;
use Redbox\GivexGiftCard\Api\Data\ResponseInterface;
use Redbox\GivexGiftCard\Api\GivexConnectorInterface;
use Redbox\GivexGiftCard\Api\GivexResponseParserInterface;
use Psr\Log\LoggerInterface;
use Zend_Http_Client_Exception;

class GivexConnector extends Connector implements GivexConnectorInterface
{
    public const XML_PATH_BASE_URL = 'givex/api/url';
    public const XML_PATH_FALLBACK_BASE_URL = 'givex/api/fallback_url';

    /**
     * Givex Response Parser.
     *
     * @var GivexResponseParserInterface
     */
    private $responseParser;

    /**
     * GivexConnector constructor.
     * @param ConnectorHelper $connectorHelper
     * @param LoggerInterface $logger
     * @param GivexResponseParserInterface $responseParser
     * @param string $requestDebugEnabledPath
     * @param string $responseDebugEnabledPath
     * @param string $requestTimeoutPath
     * @param array|null $config
     */
    public function __construct(
        ConnectorHelper $connectorHelper,
        LoggerInterface $logger,
        GivexResponseParserInterface $responseParser,
        string $requestDebugEnabledPath = '',
        string $responseDebugEnabledPath = '',
        string $requestTimeoutPath = '',
        ?array $config = null
    ) {
        parent::__construct(
            $connectorHelper,
            $logger,
            $requestDebugEnabledPath,
            $responseDebugEnabledPath,
            $requestTimeoutPath,
            $config
        );
        $this->responseParser = $responseParser;
    }

    /**
     * Get base URL.
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->fixUrl(
                $this->helper->getConfig(static::XML_PATH_BASE_URL)
            );
        }

        return $this->baseUrl;
    }

    /**
     * Send givex request.
     *
     * @param RequestInterface $request
     * @param array $keyMapping
     * @param bool $redeemRequest
     * @return ResponseInterface
     * @throws \RuntimeException
     * @throws Zend_Http_Client_Exception
     * @throws \UnexpectedValueException
     * @throws \Exception
     */
    public function sendGivexRequest(
        RequestInterface $request,
        array $keyMapping,
        $redeemRequest = false
    ): ResponseInterface {
        $data = [];
        $requestParams = $request->getParams();
        try {
            $data = $this->sendRequest($request);
        } catch (Zend_Http_Client_Exception $e) {
            $fallbackUrl = $this->helper->getConfig(static::XML_PATH_FALLBACK_BASE_URL);
            if ($fallbackUrl === null && $redeemRequest) {
                $this->logger->critical(sprintf(
                    'Unable to get request using the primary URL: %s. Fallback URL is not configured, Givex transaction failed. Sending a reversal request on primary URL',
                    $e->getMessage()
                ));
                try {
                    $reversalRequestParams = $this->updateReversalRequestParams($request->getParams());
                    $request->setParams($reversalRequestParams);
                    $this->sendRequest($request);
                    throw new \Exception('Givex transaction failed');
                } catch (Zend_Http_Client_Exception $e) {
                    $this->logger->critical(sprintf(
                        'Sending reversal request on primary URL failed %s',
                        $e->getMessage()
                    ));
                }
            } elseif ($fallbackUrl !== null && $redeemRequest) {
                $this->logger->critical(sprintf(
                    'Unable to get request using the primary URL: %s sending a reversal request on primary URL',
                    $e->getMessage()
                ));
                try {
                    $reversalRequestParams = $this->updateReversalRequestParams($request->getParams());
                    $request->setParams($reversalRequestParams);
                    $this->sendRequest($request);
                    $this->logger->notice(sprintf(
                        'Sending request on fallback URL'
                    ));
                    $request->setParams($requestParams);
                    $this->baseUrl = $this->fixUrl($fallbackUrl);
                    $data = $this->sendRequest($request);
                } catch (Zend_Http_Client_Exception $e) {
                    $this->logger->notice(sprintf(
                        'Sending request on fallback URL'
                    ));
                    $request->setParams($requestParams);
                    $this->baseUrl = $this->fixUrl($fallbackUrl);
                    try {
                        $data = $this->sendRequest($request);
                    } catch (Zend_Http_Client_Exception $e) {
                        $this->logger->critical(sprintf(
                            'Unable to get request using the fallback URL, Givex transaction failed: %s ',
                            $e->getMessage()
                        ));
                        throw new \Exception('Givex transaction failed');
                    }
                }
            } elseif ($fallbackUrl !== null) {
                $this->logger->critical(sprintf(
                    'Unable to get request on primary URL. Using the fallback URL %s',
                    $e->getMessage()
                ));
                $this->baseUrl = $this->fixUrl($fallbackUrl);
                try {
                    $data = $this->sendRequest($request);
                } catch (Zend_Http_Client_Exception $e) {
                    $this->logger->critical(sprintf(
                        'Unable to get request using the fallback URL, transaction failed: %s',
                        $e->getMessage()
                    ));
                    throw new \Exception('Givex transaction failed');
                }
            }
        }
        return $this->responseParser->parseResponse($keyMapping, $data);
    }

    /**
     * @param array $params
     * @return mixed
     */
    private function updateReversalRequestParams($params): array
    {
        $params['method'] = \Redbox\GivexGiftCard\Api\ReversalInterface::METHOD;
        return $params;
    }
}
