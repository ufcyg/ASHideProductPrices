const { Component } = Shopware;
// Override the 'sw-product-detail' component
Component.override('sw-product-detail', {
    // Override the computed property 'productCriteria' and add the bundles assocciation criteria
    computed: {
        productCriteria() {
            const criteria = this.$super('productCriteria');
            criteria.addAssociation('customergroups');

            return criteria;
        },
    }

});