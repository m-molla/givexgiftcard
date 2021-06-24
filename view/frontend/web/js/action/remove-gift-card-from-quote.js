
define([
    'jquery',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/error-processor',
    'Redbox_GivexGiftCard/js/model/payment/gift-card-messages',
    'mage/translate'
], function (
    $,
    urlBuilder,
    storage,
    customer,
    quote,
    getPaymentInformationAction,
    fullScreenLoader,
    errorProcessor,
    messageList
) {
    'use strict';

    return function (giftCardCode) {
        var serviceUrl,
                message = $.mage.__('Gift Card %1 was removed.').replace('%1', giftCardCode);

        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/guest-carts/:cartId/givexGiftCards/:giftCardCode', {
                cartId: quote.getQuoteId(),
                giftCardCode: giftCardCode
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/giftCards/:givexGiftCardCode', {
                giftCardCode: giftCardCode
            });
        }

        messageList.clear();
        fullScreenLoader.startLoader();

        return storage.delete(
                serviceUrl
                ).done(
                function (response) {
                    if (response) {
                        $.when(getPaymentInformationAction()).always(function () {
                            fullScreenLoader.stopLoader();
                        });
                        messageList.addSuccessMessage({
                            'message': message
                        });
                    }
                }
        ).fail(
                function (response) {
                    errorProcessor.process(response, messageList);
                    fullScreenLoader.stopLoader();
                }
        );
    };
}
);
