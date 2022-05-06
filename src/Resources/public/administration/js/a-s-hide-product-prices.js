(this.webpackJsonp=this.webpackJsonp||[]).push([["a-s-hide-product-prices"],{NvCP:function(e,t){e.exports='{% block sw_product_detail_attribute_sets %}\n    {% parent() %}\n    {# TODO: add a card for the customergroups #}\n    <sw-card :title="$t(\'sw-product.detail.customerGroupCardLabel\')"\n             :isLoading="isLoading">\n        {# TODO: add an \'entity-many-to-many\' select for the customergroups, which is wrapped in an \'inherit-wrapper\' https://component-library.shopware.com/components/sw-inherit-wrapper #}\n        <sw-inherit-wrapper v-if="!isLoading"\n                            v-model="product.extensions.customergroups"\n                            :inheritedValue="parentProduct.extensions ? parentProduct.extensions.customergroups : null"\n                            :hasParent="!!parentProduct.id"\n                            :label="$t(\'sw-product.detail.customerGroupSelectLabel\')"\n                            isAssociation\n                            @inheritance-remove="saveProduct"\n                            @inheritance-restore="saveProduct">\n            <template #content="{ currentValue, isInherited, updateCurrentValue }">\n                <sw-entity-many-to-many-select {# https://component-library.shopware.com/components/sw-entity-many-to-many-select #}\n                    :localMode="product.isNew()"\n                    :entityCollection="currentValue"\n                    @input="updateCurrentValue"\n                    labelProperty="name"\n                    :disabled="isInherited"\n                    :key="isInherited"\n                    :placeholder="$t(\'sw-product.detail.customerGroupSelectPlaceholder\')">\n                </sw-entity-many-to-many-select>\n            </template>\n        </sw-inherit-wrapper>\n    </sw-card>\n{% endblock %}'},PsaX:function(e,t,r){"use strict";r.r(t);r("dK0M");var n=r("NvCP"),o=r.n(n);Shopware.Component.override("sw-product-detail-base",{template:o.a,computed:{productRepository:function(){return this.repositoryFactory.create("product")}},methods:{saveProduct:function(){this.product&&this.productRepository.save(this.product,Shopware.Context.api)}}})},dK0M:function(e,t){Shopware.Component.override("sw-product-detail",{computed:{productCriteria:function(){var e=this.$super("productCriteria");return e.addAssociation("customergroups"),e}}})}},[["PsaX","runtime"]]]);