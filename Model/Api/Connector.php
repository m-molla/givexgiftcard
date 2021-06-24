<?php

namespace Redbox\GivexGiftCard\Model\Api;

use Redbox\GivexGiftCard\Api\Data\RequestInterface;
use Redbox\GivexGiftCard\Api\ConnectorInterface;
use Redbox\GivexGiftCard\Helper\ConnectorHelper;
use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\ZendClient;

abstract class Connector extends ZendClient implements ConnectorInterface
{
    /**
     * Helper.
     *
     * @var ConnectorHelper
     */
    protected $helper;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Endpoint URI.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * XML path to the 'Enable request logging' config field.
     *
     * @var string
     */
    protected $requestDebugEnabledPath;

    /**
     * XML path to the 'Enable response logging' config field.
     *
     * @var string
     */
    protected $responseDebugEnabledPath;

    /**
     * XML path to the 'Request Timeout' config field.
     *
     * @var string
     */
    protected $requestTimeoutPath;

    /**
     * Connector constructor.
     *
     * @param ConnectorHelper $connectorHelper
     * @param LoggerInterface $logger
     * @param string          $requestDebugEnabledPath
     * @param string          $responseDebugEnabledPath
     * @param string          $requestTimeoutPath
     * @param array|null      $config
     */
    public function __construct(
        ConnectorHelper $connectorHelper,
        LoggerInterface $logger,
        string $requestDebugEnabledPath = '',
        string $responseDebugEnabledPath = '',
        string $requestTimeoutPath = '',
        array $config = null
    ) {
        parent::__construct(null, $config);
        $this->helper = $connectorHelper;
        $this->logger = $logger;
        $this->requestDebugEnabledPath = $requestDebugEnabledPath;
        $this->responseDebugEnabledPath = $responseDebugEnabledPath;
        $this->requestTimeoutPath = $requestTimeoutPath;
    }

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
    ): array {
        $this->prepareRequest($request);
        $this->logRequest($request);
        $response = $this->request($request->getMethod());

        if ($this->isResponseDebugEnabled()) {
            $this->logger->notice(
                __('Response: %1', $response->asString())
            );
        }

        if (static::CORRECT_RESPONSE_CODE_REQUIRED
            && $response->getStatus() !== static::CORRECT_RESPONSE_CODE
        ) {
            throw new \RuntimeException(__(
                'Error: status %1, Message: %2',
                $response->getStatus(),
                $response->getRawBody()
            )->render());
        }

        return $this->parseResponse($response);
    }

    /**
     * Parses response.
     *
     * @param \Zend_Http_Response $response
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function parseResponse(\Zend_Http_Response $response): array
    {
        $parsedData = [];

        if (static::RESPONSE_PARSING_REQUIRED) {
            $body = $response->getBody();
            $parsedData = $this->helper->decodeJson($body);
        }

        return $parsedData;
    }

    /**
     * Log the request if logging is enabled.
     *
     * @param RequestInterface $request
     * @return void
     */
    protected function logRequest(RequestInterface $request): void
    {
        if (!$this->isRequestDebugEnabled()) {
            return;
        }

        $this->logger->notice(
            __('METHOD: %1', $request->getMethod())
        );
        $this->logger->notice(__(
            'ENDPOINT: %1',
            $this->getEndpointUrl($request->getUri())
        ));
        $params = $request->getParams();

        if (!is_array($params)) {
            $params = [$params];
        }

        $requestParams = array_merge(
            $params,
            $request->getGetParams()
        );
        $this->logger->notice(
            __('REQUEST: %1', $this->helper->encodeJson($requestParams))
        );
    }

    /**
     * Reformat given URL to guarantree trailing slash
     *
     * @param string $url
     * @return string
     */
    protected function fixUrl(string $url): string
    {
        return rtrim($url, '/') . '/';
    }

    /**
     * Prepare Request.
     *
     * @param  RequestInterface $request
     * @param  bool $preventParamsSetting
     * @return void
     * @throws \Zend_Http_Client_Exception
     * @throws \InvalidArgumentException
     */
    protected function prepareRequest(
        RequestInterface $request,
        bool $preventParamsSetting = false
    ): void {
        $this->resetParameters();
        $this->setHeaders($request->getHeaders());
        $this->setHeaders(
            static::CONTENT_TYPE_KEY,
            static::CONTENT_TYPE_VALUE
        );
        $this->setUri($this->getEndpointUrl($request->getUri()));

        if (!$preventParamsSetting) {
            $params = $request->getParams();

            if (!empty($params)) {
                $query = $this->helper->encodeJson($params);
                $this->setRawData($query);
            }

            foreach ($request->getGetParams() as $key => $value) {
                $this->_setParameter('GET', $key, $value);
            }
        }

        if (!empty($this->requestTimeoutPath)) {
            $this->config['timeout'] = (int)$this->helper->getConfig($this->requestTimeoutPath);
        }
    }

    /**
     * Get Api Url.
     *
     * @param string $uri
     * @return string
     */
    protected function getEndpointUrl(string $uri): string
    {
        return $this->getBaseUrl() . $uri;
    }

    /**
     * Get base URL.
     *
     * @return string
     */
    protected function getBaseUrl(): string
    {
        if (!$this->baseUrl) {
            $this->baseUrl = $this->fixUrl(static::BASE_URL);
        }

        return $this->baseUrl;
    }

    /**
     * Check if request debug mode is enabled.
     *
     * @return bool
     */
    protected function isRequestDebugEnabled(): bool
    {
        return $this->helper->getFlag($this->requestDebugEnabledPath);
    }

    /**
     * Check if response debug mode is enabled.
     *
     * @return bool
     */
    protected function isResponseDebugEnabled(): bool
    {
        return $this->helper->getFlag($this->responseDebugEnabledPath);
    }
}
