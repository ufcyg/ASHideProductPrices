<?php declare(strict_types=1);

namespace ASHideProductPrices\Extension\Checkout\Customer\Aggregate\CustomerGroup;

use ASHideProductPrices\Core\Content\ProductCustomergroupMappingDefinition;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
/**
 * 
 * https://developer.shopware.com/docs/guides/plugins/plugins/framework/data-handling/add-complex-data-to-existing-entities
 * 
 * 
 * We want to extend the product entity, so we create a subdirectory Content/Product/ since the entity 
 * is located there in the Core. Our class then has to extend from the abstract Shopware\Core\Framework\DataAbstractionLayer\EntityExtension class, 
 * which forces you to implement the getDefinition method. 
 * It has to point to the entity definition you want to extend, so ProductDefinition in this case. 
 *
 */
class ProductAssociationExtension extends EntityExtension{

    /**
     * add new fields by overriding the method extendFields and add your new fields in there
     */
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            /**
             * Since you must not extend the product table with a new column, 
             * you'll have to add a new table which contains the new data for the product. 
             * This new table will then be associated using a OneToOne association.
             */
            // new OneToOneAssociationField('exampleExtension' /**propertyName */, 
            //                              'id' /** storageName of column in the database for the extended entity */,
            //                              'product_id', /** referenceField the column in the other database table which shall be linked */ 
            //                              ProductBundleExtensionDefinition::class /** The class name of the definition that we want to connect via the association */, 
            //                              true /** autoload */)
            (new ManyToManyAssociationField(
                'products', /** name of the property in your entity, that will contain the associated entities */
                ProductDefinition::class, /** class of the associated definition */
                ProductCustomergroupMappingDefinition::class, /** class of the mapping definition */
                'customer_id', /** name of the id column for the current entity */
                'customer_id' /** name of the id column for the referenced entity */ //caution!!
            ))->addFlags(new Inherited())            
        );
    }

    /**
     * Has to point to the entity definition you want to extend, so CustomerGroupDefinition in this case.
     */
    public function getDefinitionClass(): string
    {
        return CustomerGroupDefinition::class;
    }
}
/**
 * We have to register our extension via the DI-container.
 */