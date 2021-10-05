<?php declare(strict_types=1);

namespace ASHideProductPrices\Core\Content;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;

/**
 * 
 * https://developer.shopware.com/docs/guides/plugins/plugins/framework/data-handling/add-data-associations
 * 
 * ManyToMany associations require another, third entity to be available. 
 * It will be called ProductBundleMappingDefinition and is responsible for connecting both definitions. 
 * It also needs an own database table
 */

class ProductCustomergroupMappingDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'as_product_customergroup_mapping';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('customer_group_id', /** name of the column in the database */
                         'customerGroupId', /** the property name in the definition class */
                         CustomerGroupDefinition::class /** respective definition class */
                         ))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', /** name of the column in the database */
                         'productId', /** the property name in the definition class */
                         ProductDefinition::class /** respective definition class */
                         ))->addFlags(new PrimaryKey(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('customergroups', /** name of the property in your entity, which should contain the entries */
                                          'customer_group_id', /** name of the column in the database */
                                          CustomerGroupDefinition::class, /** respective definition class */
                                          'id'),
            new ManyToOneAssociationField('products', /** name of the property in your entity, which should contain the entries */
                                          'product_id', /** name of the column in the database */
                                          ProductDefinition::class, /** respective definition class */
                                          'id'),
            new CreatedAtField(),
        ]);
    }
}