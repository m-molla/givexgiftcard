
define([
    'jquery',
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'mage/url',
    'Magento_Checkout/js/model/totals',
    'Redbox_GivexGiftCard/js/action/remove-gift-card-from-quote'
], function ($, ko, generic, url, totals, removeAction) {
    'use strict';

    return generic.extend({
        defaults: {
            template: 'Redbox_GivexGiftCard/summary/gift-card-account'
        },

        /**
         * Get information about applied gift cards and their amounts
         *
         * @returns {Array}.
         */
        getAppliedGiftCards: function () {
            if (totals.getSegment('givexgiftcardaccount')) {
                return JSON.parse(totals.getSegment('givexgiftcardaccount')['extension_attributes']['givex_gift_cards']);
            }

            return [];
        },

        /**
         * @return {Object|Boolean}
         */
        isAvailable: function () {
            return this.isFullMode() && totals.getSegment('givexgiftcardaccount') &&
                totals.getSegment('givexgiftcardaccount').value != 0; //eslint-disable-line eqeqeq
        },

        /**
         * @param {Number} usedBalance
         * @return {*|String}
         */
        getAmount: function (usedBalance) {
            return this.getFormattedPrice(usedBalance);
        },

        /**
         * @param {String} giftCardCode
         * @param {Object} event
         */
        removeGiftCard: function (giftCardCode, event) {
            event.preventDefault();

            if (giftCardCode) {
                removeAction(giftCardCode);
            }
        }
    });
});
