<?php

namespace Redbox\GivexGiftCard\Model\Api;

use Magento\Framework\Encryption\EncryptorInterface;
use Redbox\GivexGiftCard\Api\Data\RequestInterface;
use Redbox\GivexGiftCard\Api\Data\RequestInterfaceFactory;
use Redbox\GivexGiftCard\Api\GivexRequestBuilderInterface;
use Redbox\GivexGiftCard\Helper\ConnectorHelper;
use UnexpectedValueException;
use Zend\Http\Request as HttpRequest;

class GivexRequestBuilder implements GivexRequestBuilderInterface
{
    /**
     * Request Factory.
     *
     * @var RequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * Connector Helper.
     *
     * @var ConnectorHelper
     */
    private $connectorHelper;

    /**
     * @var EncryptorInterface
     */
    private $enc;

    /**
     * GivexRequestBuilder constructor.
     *
     * @param RequestInterfaceFactory $requestFactory
     * @param ConnectorHelper         $connectorHelper
     */
    public function __construct(
        RequestInterfaceFactory $requestFactory,
        ConnectorHelper         $connectorHelper,
        EncryptorInterface $enc
    ) {
        $this->requestFactory  = $requestFactory;
        $this->connectorHelper = $connectorHelper;
        $this->enc = $enc;
    }

    /**
     * Build GiveX request.
     *
     * @param  string $method
     * @param  array  $params
     * @return RequestInterface
     */
    public function buildRequest(string $method, array $params): RequestInterface
    {
        $requestParams = $this->getBaseParams($method);
        $requestParams['params'] = $params;

        return $this->requestFactory->create([
            RequestInterface::METHOD => HttpRequest::METHOD_POST,
            RequestInterface::PARAMS => $requestParams,
        ]);
    }

    /**
     * Get User ID.
     *
     * @return string
     * @throws UnexpectedValueException
     */
    public function getUserId(): string
    {
        return $this->getGivexCredentialConfig(self::XML_PATH_USERID);
    }

    /**
     * Get Password.
     *
     * @return string
     * @throws UnexpectedValueException
     */
    public function getPassword(): string
    {
        return $this->enc->decrypt($this->getGivexCredentialConfig(self::XML_PATH_PASSWORD));
    }

    /**
     * Try to get givex credentials from the config.
     *
     * @param  string $path
     * @return string
     * @throws UnexpectedValueException
     */
    private function getGivexCredentialConfig(string $path): string
    {
        $value = $this->connectorHelper->getConfig($path);

        if (empty($value)) {
            throw new UnexpectedValueException('Givex credentials are not configured');
        }

        return (string)$value;
    }

    /**
     * Get base request params that are used for every GiveX request.
     *
     * @param  string $method
     * @return array
     */
    private function getBaseParams(string $method): array
    {
        return [
            'jsonrpc' => '2.0',
            'id'      => uniqid(),
            'method'  => $method,
        ];
    }
}
