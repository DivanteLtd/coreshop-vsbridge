pimcore.registerNS("pimcore.plugin.CoreShop2VueStorefrontBundle");

pimcore.plugin.CoreShop2VueStorefrontBundle = Class.create(pimcore.plugin.admin, {
    getClassName: function () {
        return "pimcore.plugin.CoreShop2VueStorefrontBundle";
    },

    initialize: function () {
        pimcore.plugin.broker.registerPlugin(this);
    },

    pimcoreReady: function (params, broker) {
        // alert("CoreShop2VueStorefrontBundle ready!");
    }
});

var CoreShop2VueStorefrontBundlePlugin = new pimcore.plugin.CoreShop2VueStorefrontBundle();
