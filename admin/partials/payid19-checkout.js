(function() {
    "use strict";
    const settings = window.wc.wcSettings.getSetting( 'WC_Gateway_Payid19_data', {} );
    const label = window.wp.htmlEntities.decodeEntities( settings.title ) || window.wp.i18n.__( 'Payid19 Crypto Payment Gateway', 'WC_Gateway_Payid19' );
    const Content = () => {
        return window.wp.htmlEntities.decodeEntities( settings.description || '' );
    };
    const Block_Gateway = {
        name: 'WC_Gateway_Payid19',
        label: wp.element.createElement(() =>
          wp.element.createElement(
            "span",
            null,
            wp.element.createElement("img", {
              src: settings.icon,
              alt: settings.title,
            }),
            "  " + settings.title
            )
          ),
        content: Object( window.wp.element.createElement )( Content, null ),
        edit: Object( window.wp.element.createElement )( Content, null ),
        canMakePayment: () => true,
        ariaLabel: label,
        supports: {
            features: settings.supports,
        },
    };
    window.wc.wcBlocksRegistry.registerPaymentMethod( Block_Gateway );
})();