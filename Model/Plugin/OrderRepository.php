<?php

namespace Redbox\GivexGiftCard\Model\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\GiftCardAccount\Model\GiftCard;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\GiftCardAccount\Model\GiftCardFactory;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Plugin for Order repository.
 */
class OrderRepository
{
    /**
     * @var OrderExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var GiftCardFactory
     */
    private $giftCardFactory;

    /**
     * Instance of serializer.
     *
     * @var Json
     */
    private $serializer;

    /**
     * Init Plugin
     * @param OrderExtensionFactory $extensionFactory
     * @param GiftCardFactory $giftCardFactory
     * @param Json|null $serializer
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory,
        GiftCardFactory $giftCardFactory,
        Json $serializer = null
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->giftCardFactory = $giftCardFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $entity
     *
     * @return OrderInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $entity
    ) {
        if (!$entity->getGivexGiftCards()) {
            return $entity;
        }
        /** @var OrderExtension $extensionAttributes */
        $extensionAttributes = $entity->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionFactory->create();
        }

        $giftCards = $this->createGiftCards($this->serializer->unserialize($entity->getGivexGiftCards()));

        $extensionAttributes->setGivexGiftCards($giftCards);
        $extensionAttributes->setGivexBaseGiftCardsAmount($entity->getGivexBaseGiftCardsAmount());
        $extensionAttributes->setGivexGiftCardsAmount($entity->getGivexGiftCardsAmount());
        $extensionAttributes->setGivexBaseGiftCardsInvoiced($entity->getGivexBaseGiftCardsInvoiced());
        $extensionAttributes->setGivexGiftCardsInvoiced($entity->getGivexGiftCardsInvoiced());
        $extensionAttributes->setGivexBaseGiftCardsRefunded($entity->getBaseGiftCardsRefunded());
        $extensionAttributes->setGivexGiftCardsRefunded($entity->getGivexGiftCardsRefunded());

        $entity->setExtensionAttributes($extensionAttributes);

        return $entity;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $entities
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $entities
    ) {
        /** @var OrderInterface $entity */
        foreach ($entities->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }

        return $entities;
    }

    /**
     * Create Gift Cards Data Objects
     *
     * @param array $items
     * @return array
     */
    private function createGiftCards(array $items)
    {
        $giftCards = [];
        foreach ($items as $item) {
            /** @var GiftCard $giftCard */
            $giftCard = $this->giftCardFactory->create();
            $giftCard->setId($item[Giftcardaccount::ID]);
            $giftCard->setCode($item[Giftcardaccount::CODE]);
            $giftCard->setAmount($item[Giftcardaccount::AMOUNT]);
            $giftCard->setBaseAmount($item[Giftcardaccount::BASE_AMOUNT]);
            $giftCards[] = $giftCard;
        }
        return $giftCards;
    }
}
