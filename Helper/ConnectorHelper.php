<?php

namespace Redbox\GivexGiftCard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;

class ConnectorHelper extends AbstractHelper
{
    /**
     * JSON Serializer.
     *
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * ConnectorHelper constructor.
     *
     * @param Context              $context
     * @param JsonSerializer       $jsonSerializer
     */
    public function __construct(
        Context              $context,
        JsonSerializer       $jsonSerializer
    ) {
        parent::__construct($context);
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Try to serialize an array.
     *
     * @param mixed $data
     * @return bool|string
     * @throws \InvalidArgumentException
     */
    public function encodeJson($data)
    {
        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Try to unserialize a string.
     *
     * @param string $data
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     */
    public function decodeJson(string $data)
    {
        return $this->jsonSerializer->unserialize($data);
    }

    /**
     * Get field value by the provided config path.
     *
     * @param  string $path
     * @return null|string
     */
    public function getConfig(string $path): ?string
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get flag by the provided config path.
     *
     * @param  string $path
     * @return bool
     */
    public function getFlag(string $path): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_WEBSITE
        );
    }
}
