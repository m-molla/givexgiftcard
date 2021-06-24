<?php

namespace Redbox\GivexGiftCard\Model\Total\Quote;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\GiftCardAccount\Model\Giftcardaccount as ModelGiftcardaccount;
use Magento\GiftCardAccount\Model\GiftcardaccountFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface;
use Redbox\GivexGiftCard\Helper\Data;

class Giftcardaccount extends AbstractTotal
{
    protected $helper;

    /**
     * Gift card account giftcardaccount
     *
     * @var GiftcardaccountFactory
     */
    protected $_giftCAFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        Data $helper,
        GiftcardaccountFactory $giftCAFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->_giftCAFactory = $giftCAFactory;
        $this->helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->setCode('givexgiftcardaccount');
    }

    /**
     * Collect giftcertificate totals for specified address
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        $baseAmountLeft = $quote->getGivexBaseGiftCardsAmount() - $quote->getBaseGivexGiftCardsAmountUsed();
        $amountLeft = $quote->getGivexGiftCardsAmount() - $quote->getGiftCardsGivexAmountUsed();

        if ($baseAmountLeft >= $total->getBaseGrandTotal()) {
            $baseUsed = $total->getBaseGrandTotal();
            $used = $total->getGrandTotal();

            $total->setBaseGrandTotal(0);
            $total->setGrandTotal(0);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseAmountLeft);
            $total->setGrandTotal($total->getGrandTotal() - $amountLeft);
        }

        $addressCards = [];
        $usedAddressCards = [];
        if ($baseUsed) {
            $quoteCards = $this->_sortGiftCards($this->helper->getCards($quote));
            $skipped = 0;
            $baseSaved = 0;
            $saved = 0;
            foreach ($quoteCards as $quoteCard) {
                $card = $quoteCard;

                if ($quoteCard[GiftCardDataInterface::BASE_AMOUNT] + $skipped <= $quote->getGivexBaseGiftCardsAmountUsed()
                ) {
                    $baseThisCardUsedAmount = $thisCardUsedAmount = 0;
                } elseif ($quoteCard[GiftCardDataInterface::BASE_AMOUNT] + $baseSaved > $baseUsed) {
                    $baseThisCardUsedAmount = min(
                        $quoteCard[GiftCardDataInterface::BASE_AMOUNT],
                        $baseUsed - $baseSaved
                    );
                    $thisCardUsedAmount = min(
                        $quoteCard[GiftCardDataInterface::AMOUNT],
                        $used - $saved
                    );

                    $baseSaved += $baseThisCardUsedAmount;
                    $saved += $thisCardUsedAmount;
                } elseif ($quoteCard[GiftCardDataInterface::BASE_AMOUNT] + $skipped + $baseSaved > $quote->getBaseGiftCardsAmountUsed()) {
                    $baseThisCardUsedAmount = min(
                        $quoteCard[GiftCardDataInterface::BASE_AMOUNT],
                        $baseUsed
                    );
                    $thisCardUsedAmount = min(
                        $quoteCard[GiftCardDataInterface::AMOUNT],
                        $used
                    );

                    $baseSaved += $baseThisCardUsedAmount;
                    $saved += $thisCardUsedAmount;
                } else {
                    $baseThisCardUsedAmount = $thisCardUsedAmount = 0;
                }
                // avoid possible errors in future comparisons
                $card[GiftCardDataInterface::BASE_AMOUNT] = round($baseThisCardUsedAmount, 4);
                $card[GiftCardDataInterface::AMOUNT] = round($thisCardUsedAmount, 4);

                $addressCards[] = $card;
                if ($baseThisCardUsedAmount) {
                    $usedAddressCards[] = $card;
                }
                $skipped += $quoteCard[GiftCardDataInterface::BASE_AMOUNT];
            }
        }
        $this->helper->setCards($total, $usedAddressCards);
        $total->setUsedGivexGiftCards($total->getGivexGiftCards());
        $this->helper->setCards($total, $addressCards);

        $baseTotalUsed = $quote->getGivexBaseGiftCardsAmountUsed() + $baseUsed;
        $totalUsed = $quote->getGivexGiftCardsAmountUsed() + $used;

        $quote->setGivexBaseGiftCardsAmountUsed($baseTotalUsed);
        $quote->setGivexGiftCardsAmountUsed($totalUsed);

        $total->setGivexBaseGiftCardsAmount($baseUsed);
        $total->setGivexGiftCardsAmount($used);

        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param Quote $quote
     * @param Quote\Address\Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total)
    {
        $giftCards = $this->helper->getCards($total);
        if (!empty($giftCards)) {
            return [
                'code' => $this->getCode(),
                'title' => __('Givex Gift Cards'),
                'value' => -$total->getGivexGiftCardsAmount(),
                'gift_cards' => $giftCards
            ];
        }

        return null;
    }

    /**
     * @param array $in
     * @return mixed
     */
    protected function _sortGiftCards($in)
    {
        usort($in, [$this, 'compareGiftCards']);
        return $in;
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    public static function compareGiftCards($a, $b)
    {
        if ($a[GiftCardDataInterface::BASE_AMOUNT] == $b[GiftCardDataInterface::BASE_AMOUNT]) {
            return 0;
        }
        return $a[GiftCardDataInterface::BASE_AMOUNT] > $b[GiftCardDataInterface::BASE_AMOUNT] ? 1 : -1;
    }
}
