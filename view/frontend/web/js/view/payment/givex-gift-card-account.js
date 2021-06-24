
define([
    'jquery',
    'ko',
    'uiComponent',
    'Redbox_GivexGiftCard/js/action/set-gift-card-information',
    'Redbox_GivexGiftCard/js/action/get-gift-card-information',
    'Magento_Checkout/js/model/totals',
    'Redbox_GivexGiftCard/js/model/gift-card',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'mage/validation'
], function ($, ko, Component, setGiftCardAction, getGiftCardAction, totals, giftCardAccount, quote, priceUtils) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Redbox_GivexGiftCard/payment/givex-gift-card-account',
            giftCardCode: '',
            giftCardPin: ''
        },
        isLoading: getGiftCardAction.isLoading,
        giftCardAccount: giftCardAccount,

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['giftCardCode','giftCardPin']);

            return this;
        },

        /**
         * Set gift card.
         */
        setGiftCard: function () {
            if (this.validate()) {
                setGiftCardAction([{"card_number": this.giftCardCode(), "security_code": this.giftCardPin()}]);
            }
        },

        /**
         * Check balance.
         */
        checkBalance: function () {
            if (this.validate()) {
                getGiftCardAction.check(this.giftCardCode());
            }
        },

        /**
         * @param {*} price
         * @return {String|*}
         */
        getAmount: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = '#givexgiftcard-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
