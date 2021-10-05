import template from './sw-product-detail-base.html.twig';

const { Component } = Shopware;
// TODO: Override the 'sw-product-detail-base' component and add template
Component.override('sw-product-detail-base', {
    template,

    computed: {
        productRepository() {
            return this.repositoryFactory.create('product');
        },
    },
    // TODO: add a 'saveProduct()' method, that uses the productRepository to save the current product
    methods: {
        saveProduct() {
            if (this.product) {
                this.productRepository.save(this.product, Shopware.Context.api);
            }
        }
    }

});
