<?php

declare(strict_types=1);

namespace Redbox\GivexGiftCard\Model\Plugin;

use Exception;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector as QuoteTotalsCollector;
use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface;
use Redbox\GivexGiftCard\Api\GiftCardAccountManagerInterface;
use Redbox\GivexGiftCard\Helper\Data;
use Redbox\GivexGiftCard\Model\GiftCardData;

/**
 * Plugin to make right collection for Gift Card Accounts
 */
class TotalsCollector
{
    protected $helper;


    protected $manager;

    protected $giftCardDataFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;


    public function __construct(
        Data $helper,
        GiftCardAccountManagerInterface $manager,
        \Redbox\GivexGiftCard\Model\GiftCardDataFactory $giftCardDataFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->manager = $manager;
        $this->helper = $helper;
        $this->giftCardDataFactory = $giftCardDataFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Apply before collect
     *
     * @param QuoteTotalsCollector $subject
     * @param Quote $quote
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCollect(
        QuoteTotalsCollector $subject,
        Quote $quote
    ) {
        $this->resetGiftCardAmount($quote);
    }

    /**
     * Apply before collectQuoteTotals
     *
     * @param QuoteTotalsCollector $subject
     * @param Quote $quote
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCollectQuoteTotals(
        QuoteTotalsCollector $subject,
        Quote $quote
    ) {
        $this->resetGiftCardAmount($quote);
    }

    /**
     * Reset quote Gift Card Accounts amount
     *
     * @param Quote $quote
     * @return void
     */
    private function resetGiftCardAmount(Quote $quote): void
    {
        $quote->setGivexBaseGiftCardsAmount(0);
        $quote->setGivexGiftCardsAmount(0);

        $quote->setGivexBaseGiftCardsAmountUsed(0);
        $quote->setGivexGiftCardsAmountUsed(0);

        $baseAmount = 0;
        $amount = 0;
        $cards = $this->helper->getCards($quote);
        foreach ($cards as $k => &$card) {
            $model = $this->reloadGiftCardData($card);

            if ($model === null || $model->getBalance() == 0) {
                unset($cards[$k]);
            } elseif ($model->getBalance() != $card[GiftCardDataInterface::BASE_AMOUNT]) {
                $card[GiftCardDataInterface::BASE_AMOUNT] = $model->getBalance();
                $baseAmount += $card[GiftCardDataInterface::BASE_AMOUNT];
                $amount += $card[GiftCardDataInterface::AMOUNT];
            } else {
                $card[GiftCardDataInterface::AMOUNT] = $this->priceCurrency->round(
                    $this->priceCurrency->convert(
                        $card[GiftCardDataInterface::BASE_AMOUNT],
                        $quote->getStore()
                    )
                );
                $baseAmount += $card[GiftCardDataInterface::BASE_AMOUNT];
                $amount += $card[GiftCardDataInterface::AMOUNT];
            }
        }
        if (!empty($cards)) {
            $this->helper->setCards($quote, $cards);
        }

        $quote->setGivexBaseGiftCardsAmount($baseAmount);
        $quote->setGivexGiftCardsAmount($amount);
    }

    private function reloadGiftCardData($giftCardData)
    {
        try {
            /** @var GiftCardData $model */
            $model = $this->giftCardDataFactory->create();
            $model->addData($giftCardData);
            return $this->manager->requestByCode($model);
        } catch (Exception $ex) {
            return null;
        }
    }
}
